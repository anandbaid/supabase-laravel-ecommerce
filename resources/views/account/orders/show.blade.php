@extends('layouts.app')
@section('title', 'Order ' . $order->order_number . " - Let's Shop")

@section('content')
@php $money = fn ($n) => '$' . number_format($n, 2); @endphp
<div class="max-w-3xl mx-auto px-4 py-10">
    <a href="{{ route('account.orders.index') }}" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:underline mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to My Orders
    </a>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-2">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Order {{ $order->order_number }}</h1>
                <p class="text-sm text-gray-500">Placed {{ $order->created_at->format('d M Y, g:i A') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $order->statusColor() }}" data-order-status-for="{{ $order->order_number }}">{{ ucfirst($order->status) }}</span>
                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}" data-payment-status-for="{{ $order->order_number }}">
                    {{ $order->payment_status === 'paid' ? 'Paid' : 'Payment pending' }}
                </span>
            </div>
        </div>

        <div class="border-t border-gray-100 mt-4 pt-4">
            <h2 class="font-semibold text-slate-900 mb-3">Items</h2>
            <div class="space-y-3">
                @foreach($order->items as $item)
                    <div class="flex justify-between text-sm">
                        <span>{{ $item->product_name }} <span class="text-gray-400">&times; {{ $item->quantity }}</span></span>
                        <span class="font-medium">{{ $money($item->subtotal) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-gray-100 mt-4 pt-4 space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>{{ $money($order->subtotal ?: $order->total) }}</span></div>
            @if($order->coupon_code)
                <div class="flex justify-between text-green-600"><span>Discount ({{ $order->coupon_code }})</span><span>-{{ $money($order->discount_amount) }}</span></div>
            @endif
            <div class="flex justify-between"><span class="text-gray-500">Shipping</span><span>{{ $order->shipping_amount > 0 ? $money($order->shipping_amount) : 'Free' }}</span></div>
            @if($order->tax_amount > 0)
                <div class="flex justify-between"><span class="text-gray-500">Tax</span><span>{{ $money($order->tax_amount) }}</span></div>
            @endif
        </div>
        <div class="border-t border-gray-100 mt-2 pt-4 flex justify-between font-bold">
            <span>Total</span><span class="text-blue-600">{{ $money($order->total) }}</span>
        </div>

        <div class="border-t border-gray-100 mt-4 pt-4 grid sm:grid-cols-2 gap-4 text-sm">
            <div>
                <h3 class="font-semibold text-slate-900 mb-1">Shipping address</h3>
                <p class="text-gray-600 whitespace-pre-line">{{ $order->shipping_address }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-slate-900 mb-1">Payment method</h3>
                <p class="text-gray-600">{{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Card (Stripe)' }}</p>
                @if($order->notes)
                    <h3 class="font-semibold text-slate-900 mt-3 mb-1">Order notes</h3>
                    <p class="text-gray-600 whitespace-pre-line">{{ $order->notes }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
