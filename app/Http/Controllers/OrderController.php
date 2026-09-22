<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

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
}
