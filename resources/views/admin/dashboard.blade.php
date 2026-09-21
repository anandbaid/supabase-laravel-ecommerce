@extends('layouts.admin')
@section('page-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="text-sm text-gray-500 mb-1">Total Sales</div>
        <div class="text-2xl font-bold">${{ number_format($totalSales, 2) }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="text-sm text-gray-500 mb-1">Total Orders</div>
        <div class="text-2xl font-bold">{{ $totalOrders }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="text-sm text-gray-500 mb-1">Total Customers</div>
        <div class="text-2xl font-bold">{{ $totalCustomers }}</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="text-sm text-gray-500 mb-1">Total Products</div>
        <div class="text-2xl font-bold">{{ $totalProducts }}</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-5 lg:col-span-1">
        <h3 class="font-semibold mb-4">Top Selling Products</h3>
        <div class="space-y-3">
            @forelse($topProducts as $i => $p)
                <div class="flex items-center justify-between text-sm">
                    <div>
                        <div class="font-medium">{{ $p->product_name }}</div>
                        <div class="text-xs text-gray-400">{{ $p->sold }} sold</div>
                    </div>
                    <div class="font-semibold">${{ number_format($p->revenue, 2) }}</div>
                </div>
            @empty
                <p class="text-gray-400 text-sm">No sales yet.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5 lg:col-span-2">
        <h3 class="font-semibold mb-4">Recent Orders</h3>
        <table class="w-full text-sm">
            <thead class="text-gray-400 text-left">
                <tr><th class="pb-2">Order</th><th class="pb-2">Amount</th><th class="pb-2">Status</th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse($recentOrders as $order)
                    <tr>
                        <td class="py-2">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-medium hover:text-blue-600">#{{ $order->order_number }}</a>
                            <div class="text-xs text-gray-400">{{ $order->customer_name }}</div>
                        </td>
                        <td class="py-2">${{ number_format($order->total, 2) }}</td>
                        <td class="py-2"><span class="px-2 py-1 rounded-full text-xs {{ $order->statusColor() }}">{{ ucfirst($order->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center text-gray-400">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-5">
    <h3 class="font-semibold mb-4">Recent Customers</h3>
    <table class="w-full text-sm">
        <thead class="text-gray-400 text-left">
            <tr><th class="pb-2">Name</th><th class="pb-2">Email</th><th class="pb-2">Orders</th><th class="pb-2">Total Spent</th></tr>
        </thead>
        <tbody class="divide-y">
            @forelse($recentCustomers as $c)
                <tr>
                    <td class="py-2 font-medium">{{ $c->name }}</td>
                    <td class="py-2 text-gray-500">{{ $c->email }}</td>
                    <td class="py-2">{{ $c->orders_count }}</td>
                    <td class="py-2">${{ number_format($c->total_spent ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-4 text-center text-gray-400">No customers yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
