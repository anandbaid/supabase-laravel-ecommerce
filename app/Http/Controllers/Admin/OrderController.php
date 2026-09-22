<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusUpdated;
use App\Models\Order;
use App\Services\OrderRefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'ilike', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'ilike', '%' . $request->search . '%');
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items.product');
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'required|in:unpaid,paid,failed,refunded',
        ]);

        $data = $request->only('status', 'payment_status');
        $previousStatus = $order->status;

        // Stamp delivered_at the first time an order is marked delivered —
        // this is what starts the customer's 7-day return window.
        if ($data['status'] === 'delivered' && !$order->delivered_at) {
            $data['delivered_at'] = now();
        }

        $order->update($data);

        if ($order->status !== $previousStatus) {
            try {
                Mail::to($order->customer_email)->send(new OrderStatusUpdated($order, $previousStatus));
            } catch (Throwable $e) {
                Log::error('Order status update email failed to send: ' . $e->getMessage(), ['exception' => $e]);
            }
        }

        return back()->with('success', 'Order updated.');
    }

    /** Approve a pending return request — doesn't refund yet, just greenlights it. */
    public function approveReturn(Order $order)
    {
        if ($order->return_status !== 'requested') {
            return back()->with('error', 'This order has no pending return request.');
        }

        $order->update([
            'return_status' => 'approved',
            'return_decided_at' => now(),
        ]);

        return back()->with('success', 'Return approved. You can now process the refund once the item is received.');
    }

    public function rejectReturn(Order $order)
    {
        if ($order->return_status !== 'requested') {
            return back()->with('error', 'This order has no pending return request.');
        }

        $order->update([
            'return_status' => 'rejected',
            'return_decided_at' => now(),
        ]);

        return back()->with('success', 'Return request rejected.');
    }

    /**
     * Process the refund for an approved return (or any order, for manual
     * ad-hoc refunds). Refunds via Stripe automatically for card payments;
     * for Cash on Delivery orders there's nothing for Stripe to refund, so
     * this just marks the order as refunded for your own records.
     */
    public function refund(Order $order, OrderRefundService $refunds)
    {
        if ($order->payment_status === 'refunded') {
            return back()->with('error', 'This order has already been refunded.');
        }

        $wasStripePayment = $order->isRefundableViaStripe();

        try {
            $refunded = $refunds->refund($order);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (!$refunded) {
            // Not a Stripe payment (e.g. COD) — nothing to call Stripe for,
            // just record it as refunded manually.
            $order->update([
                'payment_status' => 'refunded',
                'refund_amount' => $order->total,
                'refunded_at' => now(),
            ]);
        }

        if ($order->return_status === 'approved') {
            $order->update(['return_status' => 'refunded']);
        }

        return back()->with('success', 'Refund recorded' . ($wasStripePayment ? ' and processed via Stripe.' : '.'));
    }
}
