@extends('layouts.admin')
@section('page-title', 'Order #' . $order->order_number)

@section('content')
<div class="grid md:grid-cols-3 gap-6">
    <div class="md:col-span-2 bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold mb-4">Order Items</h3>
        <table class="w-full text-sm">
            <thead class="text-gray-400 text-left border-b">
                <tr><th class="pb-2">Product</th><th class="pb-2">Price</th><th class="pb-2">Qty</th><th class="pb-2 text-right">Subtotal</th></tr>
            </thead>
            <tbody class="divide-y">
                @foreach($order->items as $item)
                    <tr>
                        <td class="py-3">{{ $item->product_name }}</td>
                        <td class="py-3">${{ number_format($item->price, 2) }}</td>
                        <td class="py-3">{{ $item->quantity }}</td>
                        <td class="py-3 text-right font-medium">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="flex justify-end border-t mt-4 pt-4">
            <div class="text-right">
                <div class="text-gray-500 text-sm">Total</div>
                <div class="text-xl font-bold text-blue-600">${{ number_format($order->total, 2) }}</div>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t">
            <h3 class="font-semibold mb-3">Customer & Shipping</h3>
            <p class="text-sm"><span class="text-gray-500">Name:</span> {{ $order->customer_name }}</p>
            <p class="text-sm"><span class="text-gray-500">Email:</span> {{ $order->customer_email }}</p>
            <p class="text-sm"><span class="text-gray-500">Phone:</span> {{ $order->customer_phone ?: '—' }}</p>
            <p class="text-sm mt-2"><span class="text-gray-500">Address:</span> {{ $order->shipping_address }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 h-fit">
        <h3 class="font-semibold mb-4">Update Order</h3>
        <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="text-sm font-medium">Order Status</label>
                <select name="status" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
                    @foreach(['pending','processing','shipped','delivered','cancelled'] as $s)
                        <option value="{{ $s }}" @selected($order->status == $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Payment Status</label>
                <select name="payment_status" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
                    <option value="unpaid" @selected($order->payment_status == 'unpaid')>Unpaid</option>
                    <option value="paid" @selected($order->payment_status == 'paid')>Paid</option>
                </select>
            </div>
            <button class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium">Update Order</button>
        </form>
    </div>
</div>
@endsection
