<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderRefundService;
use Illuminate\Http\Request;
use RuntimeException;

/** Customer-facing "My Orders" — order history for the logged-in user's own orders. */
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->withCount('items')
            ->latest()
            ->paginate(8);

        return view('account.orders.index', compact('orders'));
    }

    public function show(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->with(['items', 'address'])
            ->firstOrFail();

        return view('account.orders.show', compact('order'));
    }

    /**
     * Customer-initiated cancellation — only allowed while the order hasn't
     * shipped yet. If it was paid online, the payment is refunded via
     * Stripe immediately as part of cancelling.
     */
    public function cancel(Request $request, string $orderNumber, OrderRefundService $refunds)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (!$order->canBeCancelled()) {
            return back()->with('error', 'This order can no longer be cancelled — it has already shipped.');
        }

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $data['reason'] ?? null,
        ]);

        try {
            $refunds->refund($order, 'requested_by_customer');
        } catch (RuntimeException $e) {
            // Order is cancelled either way; surface the refund issue separately
            // so support can follow up rather than leaving the order stuck.
            return back()->with('error', 'Your order was cancelled, but the automatic refund failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Your order has been cancelled.' . ($order->fresh()->payment_status === 'refunded' ? ' Your payment has been refunded.' : ''));
    }

    /**
     * Customer-initiated return request — only allowed within 7 days of
     * delivery. Doesn't refund immediately; an admin reviews and approves
     * it first (see Admin\OrderController::approveReturn).
     */
    public function requestReturn(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (!$order->canRequestReturn()) {
            return back()->with('error', 'This order is not eligible for a return. Returns must be requested within 7 days of delivery.');
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $order->update([
            'return_status' => 'requested',
            'return_reason' => $data['reason'],
            'return_requested_at' => now(),
        ]);

        return back()->with('success', 'Your return request has been submitted. We\'ll review it and get back to you shortly.');
    }
}
