@extends('layouts.admin')
@section('page-title', 'Orders')

@section('content')
<div class="flex items-center justify-between mb-5 gap-3">
    <form action="{{ route('admin.orders.index') }}" method="GET" class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search order # or customer..." class="border rounded-lg px-3 py-2 text-sm w-64">
        <select name="status" onchange="this.form.submit()" class="border rounded-lg px-3 py-2 text-sm">
            <option value="">All Status</option>
            @foreach(['pending','processing','shipped','delivered','cancelled'] as $s)
                <option value="{{ $s }}" @selected(request('status') == $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-left">
            <tr>
                <th class="px-4 py-3">Order</th>
                <th class="px-4 py-3">Customer</th>
                <th class="px-4 py-3">Amount</th>
                <th class="px-4 py-3">Payment</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($orders as $order)
                <tr>
                    <td class="px-4 py-3 font-medium">#{{ $order->order_number }}</td>
                    <td class="px-4 py-3">{{ $order->customer_name }}</td>
                    <td class="px-4 py-3">${{ number_format($order->total, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs {{ $order->paymentStatusColor() }}" data-payment-status-for="{{ $order->order_number }}">{{ $order->paymentStatusLabel() }}</span>
                    </td>
                    <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs {{ $order->statusColor() }}" data-order-status-for="{{ $order->order_number }}">{{ ucfirst($order->status) }}</span></td>
                    <td class="px-4 py-3 text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:underline">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No orders found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
