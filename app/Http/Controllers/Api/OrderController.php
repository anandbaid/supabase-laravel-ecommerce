<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderRefundService;
use Illuminate\Http\Request;
use RuntimeException;

/** The signed-in customer's own orders. */
class OrderController extends Controller
{
    public function index(Request $request)
    {
        return OrderResource::collection(
            $request->user()->orders()->withCount('items')->latest()->paginate(8)
        );
    }

    public function show(Request $request, string $orderNumber)
    {
        return new OrderResource($this->findOwn($request, $orderNumber)->load('items.product'));
    }

    /** Cancel before shipping; card payments are refunded via Stripe straight away. */
    public function cancel(Request $request, string $orderNumber, OrderRefundService $refunds)
    {
        $order = $this->findOwn($request, $orderNumber);

        if (! $order->canBeCancelled()) {
            return response()->json(['message' => 'This order can no longer be cancelled — it has already shipped.'], 422);
        }

        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $data['reason'] ?? null,
        ]);

        try {
            $refunds->refund($order, 'requested_by_customer');
        } catch (RuntimeException $e) {
            // The order is cancelled either way; surface the refund problem
            // so support can follow up.
            return response()->json([
                'message' => 'Your order was cancelled, but the automatic refund failed: ' . $e->getMessage(),
                'order' => new OrderResource($order->fresh()->load('items.product')),
            ], 200);
        }

        $order = $order->fresh()->load('items.product');

        return response()->json([
            'message' => 'Your order has been cancelled.' . ($order->payment_status === 'refunded' ? ' Your payment has been refunded.' : ''),
            'order' => new OrderResource($order),
        ]);
    }

    /** Ask for a return within 7 days of delivery; an admin reviews it. */
    public function requestReturn(Request $request, string $orderNumber)
    {
        $order = $this->findOwn($request, $orderNumber);

        if (! $order->canRequestReturn()) {
            return response()->json(['message' => 'This order is not eligible for a return. Returns must be requested within 7 days of delivery.'], 422);
        }

        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $order->update([
            'return_status' => 'requested',
            'return_reason' => $data['reason'],
            'return_requested_at' => now(),
        ]);

        return response()->json([
            'message' => 'Your return request has been submitted. We\'ll review it and get back to you shortly.',
            'order' => new OrderResource($order->fresh()->load('items.product')),
        ]);
    }

    private function findOwn(Request $request, string $orderNumber): Order
    {
        return Order::where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }
}
