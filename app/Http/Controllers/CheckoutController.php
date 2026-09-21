<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Throwable;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $summary = CartController::calculate();
        $addresses = Auth::check() ? Auth::user()->addresses()->get() : collect();
        $prefill = $addresses->firstWhere('is_default', true) ?? $addresses->first();

        return view('checkout.index', array_merge($summary, [
            'addresses' => $addresses,
            'prefill' => $prefill,
        ]));
    }

    public function store(Request $request)
    {
        $same = $request->boolean('same_as_billing');

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
        if (! $same && ! $request->filled('address_id')) {
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

        $request->validate($rules, [
            'shipping_first_name.required' => 'Please enter the recipient\'s first name.',
            'shipping_last_name.required' => 'Please enter the recipient\'s last name.',
            'shipping_line1.required' => 'Please enter the shipping street address.',
        ]);

        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $customerName = trim($request->first_name . ' ' . $request->last_name);

        $billingText = $this->formatAddress(
            $customerName,
            $request->billing_line1,
            $request->billing_line2,
            $request->billing_city,
            $request->billing_state,
            $request->billing_postal_code,
            $request->billing_country,
            $request->customer_phone
        );

        $address = null;
        if ($same) {
            $shippingText = $billingText;
        } elseif ($request->filled('address_id')) {
            $address = Address::find($request->address_id);
            if (! $address || $address->user_id !== Auth::id()) {
                return back()->withInput()->with('error', 'That address could not be used. Please choose another.');
            }
            $shippingText = $address->formatted();
        } else {
            $shippingText = $this->formatAddress(
                trim($request->shipping_first_name . ' ' . $request->shipping_last_name),
                $request->shipping_line1,
                $request->shipping_line2,
                $request->shipping_city,
                $request->shipping_state,
                $request->shipping_postal_code,
                $request->shipping_country,
                $request->shipping_phone
            );
        }

        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');
        $summary = CartController::calculate();

        if (empty($summary['items'])) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        try {
            $order = DB::transaction(function () use ($request, $cart, $products, $summary, $address, $customerName, $billingText, $shippingText) {
                $coupon = $summary['coupon'];

                $order = Order::create([
                    'user_id' => Auth::id(),
                    'customer_name' => $customerName,
                    'customer_email' => $request->customer_email,
                    'customer_phone' => $request->customer_phone,
                    'company_name' => $request->company_name,
                    'billing_address' => $billingText,
                    'shipping_address' => $shippingText,
                    'address_id' => $address?->id,
                    'subtotal' => $summary['subtotal'],
                    'tax_rate' => $summary['taxRate'],
                    'tax_amount' => $summary['taxAmount'],
                    'coupon_code' => $coupon?->code,
                    'discount_amount' => $summary['discount'],
                    'shipping_amount' => $summary['shipping'],
                    'notes' => $request->notes,
                    'total' => $summary['total'],
                    'payment_method' => $request->payment_method,
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                ]);

                foreach ($cart as $productId => $qty) {
                    if (!isset($products[$productId])) continue;
                    $product = $products[$productId];
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
            return back()->withInput()->with('error', 'Could not place your order. Please try again.');
        }

        if ($request->payment_method === 'card') {
            try {
                $checkoutUrl = $this->createStripeCheckoutSession($order);
                Session::forget(['cart', 'coupon_code']);
                return redirect()->away($checkoutUrl);
            } catch (Throwable $e) {
                Log::error('Stripe checkout session creation failed: ' . $e->getMessage(), ['exception' => $e]);
                return redirect()->route('checkout.index')->with('error', 'Could not start card payment. Please try again or choose Cash on Delivery.');
            }
        }

        Session::forget(['cart', 'coupon_code']);

        return redirect()->route('checkout.success', $order->order_number);
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

    /**
     * @throws ApiErrorException
     */
    private function createStripeCheckoutSession(Order $order): string
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems = $order->items->map(function (OrderItem $item) {
            return [
                'price_data' => [
                    'currency' => config('services.stripe.currency', 'usd'),
                    'product_data' => ['name' => $item->product_name],
                    'unit_amount' => (int) round($item->price * 100),
                ],
                'quantity' => $item->quantity,
            ];
        })->all();

        if ($order->tax_amount > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => config('services.stripe.currency', 'usd'),
                    'product_data' => ['name' => 'Tax'],
                    'unit_amount' => (int) round($order->tax_amount * 100),
                ],
                'quantity' => 1,
            ];
        }

        if ($order->shipping_amount > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => config('services.stripe.currency', 'usd'),
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
            'success_url' => route('checkout.success', $order->order_number) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.index'),
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
                'currency' => config('services.stripe.currency', 'usd'),
                'duration' => 'once',
                'name' => 'Coupon ' . $order->coupon_code,
            ]);
            $sessionParams['discounts'] = [['coupon' => $stripeCoupon->id]];
        }

        $session = StripeCheckoutSession::create($sessionParams);

        $order->update(['stripe_checkout_session_id' => $session->id]);

        return $session->url;
    }

    public function success(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->with('items')->firstOrFail();
        return view('checkout.success', compact('order'));
    }
}
