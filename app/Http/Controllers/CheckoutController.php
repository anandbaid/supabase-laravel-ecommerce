<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderPlacementException;
use App\Services\OrderPlacementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
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

    public function store(Request $request, OrderPlacementService $placement)
    {
        $request->validate(
            OrderPlacementService::rules($request->boolean('same_as_billing'), $request->filled('address_id')),
            OrderPlacementService::messages()
        );

        try {
            $order = $placement->place(
                $request->user(),
                Session::get('cart', []),
                Session::get('coupon_code'),
                $request->all()
            );
        } catch (OrderPlacementException $e) {
            return $e->cartIsEmpty
                ? redirect()->route('cart.index')->with('error', $e->getMessage())
                : back()->withInput()->with('error', $e->getMessage());
        }

        if ($request->payment_method === 'card') {
            try {
                $checkoutUrl = $placement->startCardPayment(
                    $order,
                    route('checkout.success', $order->order_number) . '?session_id={CHECKOUT_SESSION_ID}',
                    route('checkout.index')
                );
                Session::forget(['cart', 'coupon_code']);
                return redirect()->away($checkoutUrl);
            } catch (Throwable $e) {
                Log::error('Stripe checkout session creation failed: ' . $e->getMessage(), ['exception' => $e]);
                return redirect()->route('checkout.index')->with('error', 'Could not start card payment. Please try again or choose Cash on Delivery.');
            }
        }

        Session::forget(['cart', 'coupon_code']);

        // COD orders are confirmed immediately; card orders get their
        // confirmation email from the Stripe webhook once payment actually
        // succeeds, not here (the order isn't paid yet at this point).
        $placement->sendConfirmation($order);

        return redirect()->route('checkout.success', $order->order_number);
    }

    public function success(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->with('items')->firstOrFail();
        return view('checkout.success', compact('order'));
    }
}
