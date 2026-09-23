<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\CartPricing;
use App\Services\OrderPlacementException;
use App\Services\OrderPlacementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckoutController extends Controller
{
    /** Saved addresses to pre-fill the checkout form. */
    public function show(Request $request)
    {
        if ($request->user()->isAdmin()) {
            return $this->adminBlocked();
        }

        $addresses = $request->user()->addresses()->get();

        return response()->json([
            'addresses' => AddressResource::collection($addresses),
            'prefill' => ($prefill = $addresses->firstWhere('is_default', true) ?? $addresses->first())
                ? new AddressResource($prefill)
                : null,
        ]);
    }

    public function store(Request $request, OrderPlacementService $placement)
    {
        if ($request->user()->isAdmin()) {
            return $this->adminBlocked();
        }

        $input = $request->validate(
            OrderPlacementService::rules($request->boolean('same_as_billing'), $request->filled('address_id'))
                + CartController::itemRules(),
            OrderPlacementService::messages()
        );

        $cart = CartController::toCartMap($input['items']);

        // Unlike the quote endpoint, nothing is silently adjusted here: the
        // customer confirmed these quantities and totals, so any change
        // sends them back to review the cart.
        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');
        foreach ($cart as $productId => $qty) {
            $product = $products[$productId] ?? null;
            if (! $product || ! $product->is_active) {
                return $this->cartChanged('An item in your cart is no longer available. Please review your cart.');
            }
            $max = CartPricing::maxQty($product);
            if ($max < 1) {
                return $this->cartChanged("{$product->name} is out of stock. Please review your cart.");
            }
            if ($qty > $max) {
                return $this->cartChanged("Only {$max} of {$product->name} can be ordered. Please review your cart.");
            }
        }

        if (! empty($input['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper(trim($input['coupon_code'])))->first();
            $subtotal = CartPricing::quote($cart)['subtotal'];
            $error = $coupon ? $coupon->validationError($subtotal) : 'That coupon code is not valid.';
            if ($error) {
                return response()->json(['message' => $error, 'errors' => ['coupon_code' => [$error]]], 422);
            }
        }

        try {
            $order = $placement->place($request->user(), $cart, $input['coupon_code'] ?? null, $input);
        } catch (OrderPlacementException $e) {
            return response()->json(['message' => $e->getMessage()], $e->status);
        }

        if ($input['payment_method'] === 'card') {
            $frontend = rtrim((string) config('services.frontend.url'), '/');
            try {
                $checkoutUrl = $placement->startCardPayment(
                    $order,
                    $frontend . '/checkout/success/' . $order->order_number . '?session_id={CHECKOUT_SESSION_ID}',
                    $frontend . '/checkout?payment=cancelled'
                );
            } catch (Throwable $e) {
                Log::error('Stripe checkout session creation failed: ' . $e->getMessage(), ['exception' => $e]);

                return response()->json([
                    'message' => 'Could not start card payment. Please try again or choose Cash on Delivery.',
                    'order_number' => $order->order_number,
                ], 502);
            }

            return response()->json(['order_number' => $order->order_number, 'checkout_url' => $checkoutUrl], 201);
        }

        // Cash on delivery is confirmed straight away; card orders are
        // confirmed by the Stripe webhook once payment succeeds.
        $placement->sendConfirmation($order);

        return response()->json(['order_number' => $order->order_number, 'checkout_url' => null], 201);
    }

    private function adminBlocked()
    {
        return response()->json([
            'message' => 'Admin accounts cannot place orders. Please use a customer account to checkout.',
        ], 403);
    }

    private function cartChanged(string $message)
    {
        return response()->json(['message' => $message, 'errors' => ['items' => [$message]]], 409);
    }
}
