<?php

namespace App\Services;

use App\Mail\OrderConfirmed;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Throwable;

/**
 * Turns a priced cart plus the checkout form into an order. Used by both the
 * Blade checkout (cart from the session) and the JSON API (cart sent by the
 * Next.js storefront), so orders are created the same way from either.
 */
class OrderPlacementService
{
    /** Validation rules for the checkout form. */
    public static function rules(bool $sameAsBilling, bool $savedAddressChosen): array
    {
        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:30',
            'company_name' => 'nullable|string|max:150',
            'billing_country' => 'required|string|max:100',
            'billing_line1' => 'required|string|max:255',
            'billing_line2' => 'nullable|string|max:255',
            'billing_city' => 'required|string|max:100',
            'billing_state' => 'required|string|max:100',
            'billing_postal_code' => 'required|string|max:20',
            'same_as_billing' => 'nullable|boolean',
            'address_id' => 'nullable|exists:addresses,id',
            'notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cod,card',
        ];

        // A separate shipping address is only required when it differs from
        // billing AND no saved address was picked.
        if (! $sameAsBilling && ! $savedAddressChosen) {
            $rules += [
                'shipping_first_name' => 'required|string|max:100',
                'shipping_last_name' => 'required|string|max:100',
                'shipping_phone' => 'required|string|max:30',
                'shipping_country' => 'required|string|max:100',
                'shipping_line1' => 'required|string|max:255',
                'shipping_line2' => 'nullable|string|max:255',
                'shipping_city' => 'required|string|max:100',
                'shipping_state' => 'required|string|max:100',
                'shipping_postal_code' => 'required|string|max:20',
            ];
        }

        return $rules;
    }

    public static function messages(): array
    {
        return [
            'shipping_first_name.required' => 'Please enter the recipient\'s first name.',
            'shipping_last_name.required' => 'Please enter the recipient\'s last name.',
            'shipping_line1.required' => 'Please enter the shipping street address.',
        ];
    }

    /**
     * Create the order, its items, saved addresses, stock and coupon usage.
     *
     * @param  array<int|string, int>  $cart  product id => quantity
     * @param  array<string, mixed>  $input  validated checkout form fields
     *
     * @throws OrderPlacementException
     */
    public function place(User $user, array $cart, ?string $couponCode, array $input): Order
    {
        if (empty($cart)) {
            throw new OrderPlacementException('Your cart is empty.', cartIsEmpty: true);
        }

        $same = filter_var($input['same_as_billing'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $customerName = trim(($input['first_name'] ?? '') . ' ' . ($input['last_name'] ?? ''));

        $billingText = $this->formatAddress(
            $customerName,
            $input['billing_line1'],
            $input['billing_line2'] ?? null,
            $input['billing_city'],
            $input['billing_state'],
            $input['billing_postal_code'],
            $input['billing_country'],
            $input['customer_phone'] ?? null
        );

        $address = null;
        if ($same) {
            $shippingText = $billingText;
        } elseif (! empty($input['address_id'])) {
            $address = Address::find($input['address_id']);
            if (! $address || $address->user_id !== $user->id) {
                throw new OrderPlacementException('That address could not be used. Please choose another.');
            }
            $shippingText = $address->formatted();
        } else {
            $shippingText = $this->formatAddress(
                trim($input['shipping_first_name'] . ' ' . $input['shipping_last_name']),
                $input['shipping_line1'],
                $input['shipping_line2'] ?? null,
                $input['shipping_city'],
                $input['shipping_state'],
                $input['shipping_postal_code'],
                $input['shipping_country'],
                $input['shipping_phone'] ?? null
            );
        }

        $summary = CartPricing::quote($cart, $couponCode);

        if (empty($summary['items'])) {
            throw new OrderPlacementException('Your cart is empty.', cartIsEmpty: true);
        }

        try {
            return DB::transaction(function () use ($user, $input, $summary, $address, $customerName, $billingText, $shippingText, $same) {
                $userId = $user->id;
                $hadNoAddresses = ! Address::where('user_id', $userId)->exists();

                // Remember this billing address on the account, so next time the
                // customer checks out these fields are pre-filled instead of blank.
                // Matched on street + postcode, so re-using the same address updates
                // it in place rather than piling up duplicates.
                $billingAddress = Address::updateOrCreate(
                    ['user_id' => $userId, 'line1' => $input['billing_line1'], 'postal_code' => $input['billing_postal_code']],
                    [
                        'label' => 'Billing',
                        'full_name' => $customerName,
                        'phone' => $input['customer_phone'],
                        'line2' => $input['billing_line2'] ?? null,
                        'city' => $input['billing_city'],
                        'state' => $input['billing_state'],
                        'country' => $input['billing_country'],
                    ]
                );
                if ($hadNoAddresses) {
                    $billingAddress->update(['is_default' => true]);
                }

                // A hand-typed shipping address (not "same as billing" and not
                // picked from the saved list) gets saved too, so it also shows up
                // as a choice next time.
                if (! $same && ! $address) {
                    Address::updateOrCreate(
                        ['user_id' => $userId, 'line1' => $input['shipping_line1'], 'postal_code' => $input['shipping_postal_code']],
                        [
                            'label' => 'Shipping',
                            'full_name' => trim($input['shipping_first_name'] . ' ' . $input['shipping_last_name']),
                            'phone' => $input['shipping_phone'],
                            'line2' => $input['shipping_line2'] ?? null,
                            'city' => $input['shipping_city'],
                            'state' => $input['shipping_state'],
                            'country' => $input['shipping_country'],
                        ]
                    );
                }

                $coupon = $summary['coupon'];

                $order = Order::create([
                    'user_id' => $userId,
                    'customer_name' => $customerName,
                    'customer_email' => $input['customer_email'],
                    'customer_phone' => $input['customer_phone'],
                    'company_name' => $input['company_name'] ?? null,
                    'billing_address' => $billingText,
                    'shipping_address' => $shippingText,
                    'address_id' => $address?->id,
                    'subtotal' => $summary['subtotal'],
                    'tax_rate' => $summary['taxRate'],
                    'tax_amount' => $summary['taxAmount'],
                    'coupon_code' => $coupon?->code,
                    'discount_amount' => $summary['discount'],
                    'shipping_amount' => $summary['shipping'],
                    'notes' => $input['notes'] ?? null,
                    'total' => $summary['total'],
                    'payment_method' => $input['payment_method'],
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                ]);

                foreach ($summary['items'] as $line) {
                    $product = $line['product'];
                    $qty = $line['qty'];
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => $product->finalPrice(),
                        'quantity' => $qty,
                        'subtotal' => $product->finalPrice() * $qty,
                    ]);

                    $product->decrement('stock', min($qty, $product->stock));
                }

                if ($coupon) {
                    $coupon->increment('used_count');
                }

                return $order;
            });
        } catch (Throwable $e) {
            Log::error('Order creation failed: ' . $e->getMessage(), ['exception' => $e]);
            throw new OrderPlacementException('Could not place your order. Please try again.', status: 500);
        }
    }

    /**
     * Email the order confirmation (cash-on-delivery orders; card orders are
     * confirmed by the Stripe webhook once payment succeeds). Never throws.
     */
    public function sendConfirmation(Order $order): void
    {
        try {
            Mail::to($order->customer_email)->send(new OrderConfirmed($order));
        } catch (Throwable $e) {
            Log::error('Order confirmation email failed to send: ' . $e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * Create a Stripe Checkout Session for a card order and return its URL.
     *
     * @throws ApiErrorException
     */
    public function startCardPayment(Order $order, string $successUrl, string $cancelUrl): string
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $currency = config('services.stripe.currency', 'usd');

        $lineItems = $order->items->map(function (OrderItem $item) use ($currency) {
            return [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => ['name' => $item->product_name],
                    'unit_amount' => (int) round($item->price * 100),
                ],
                'quantity' => $item->quantity,
            ];
        })->all();

        if ($order->tax_amount > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => ['name' => 'Tax'],
                    'unit_amount' => (int) round($order->tax_amount * 100),
                ],
                'quantity' => 1,
            ];
        }

        if ($order->shipping_amount > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => ['name' => 'Shipping'],
                    'unit_amount' => (int) round($order->shipping_amount * 100),
                ],
                'quantity' => 1,
            ];
        }

        $sessionParams = [
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'customer_email' => $order->customer_email,
            'line_items' => $lineItems,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ];

        if ($order->discount_amount > 0) {
            // Stripe line items can't have negative amounts, so the coupon
            // discount is applied as a one-time session-level discount.
            $stripeCoupon = \Stripe\Coupon::create([
                'amount_off' => (int) round($order->discount_amount * 100),
                'currency' => $currency,
                'duration' => 'once',
                'name' => 'Coupon ' . $order->coupon_code,
            ]);
            $sessionParams['discounts'] = [['coupon' => $stripeCoupon->id]];
        }

        $session = StripeCheckoutSession::create($sessionParams);

        $order->update(['stripe_checkout_session_id' => $session->id]);

        return $session->url;
    }

    /** Build a multi-line postal address block (same shape as Address::formatted()). */
    private function formatAddress(string $name, string $line1, ?string $line2, string $city, string $state, string $zip, string $country, ?string $phone): string
    {
        return implode("\n", array_filter([
            $name,
            $line1,
            $line2,
            trim($city . ', ' . $state . ' ' . $zip, ', '),
            $country,
            $phone ? 'Phone: ' . $phone : null,
        ]));
    }
}
