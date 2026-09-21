@extends('layouts.admin')
@section('page-title', $customer->name)

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <p class="text-sm"><span class="text-gray-500">Email:</span> {{ $customer->email }}</p>
    <p class="text-sm"><span class="text-gray-500">Phone:</span> {{ $customer->phone ?: '—' }}</p>
    <p class="text-sm"><span class="text-gray-500">Joined:</span> {{ $customer->created_at->format('M d, Y') }}</p>
</div>

<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="font-semibold mb-4">Order History</h3>
    <table class="w-full text-sm">
        <thead class="text-gray-400 text-left border-b">
            <tr><th class="pb-2">Order</th><th class="pb-2">Total</th><th class="pb-2">Status</th><th class="pb-2">Date</th></tr>
        </thead>
        <tbody class="divide-y">
            @forelse($orders as $order)
                <tr>
                    <td class="py-2"><a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:underline">#{{ $order->order_number }}</a></td>
                    <td class="py-2">${{ number_format($order->total, 2) }}</td>
                    <td class="py-2"><span class="px-2 py-1 rounded-full text-xs {{ $order->statusColor() }}">{{ ucfirst($order->status) }}</span></td>
                    <td class="py-2 text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-4 text-center text-gray-400">No orders yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
