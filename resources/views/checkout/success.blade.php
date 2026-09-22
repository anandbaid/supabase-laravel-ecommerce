@extends('layouts.app')
@section('title', "Order Confirmed - Let's Shop")

@section('content')
<div class="max-w-2xl mx-auto px-4 py-16 text-center">
    <div class="text-5xl mb-4">✅</div>
    <h1 class="text-2xl font-bold mb-2">Thank you, {{ $order->customer_name }}!</h1>
    <p class="text-gray-500 mb-6">Your order <span class="font-semibold text-blue-600">#{{ $order->order_number }}</span> has been placed successfully.</p>

    <div class="flex items-center justify-center gap-3 mb-6 text-sm">
        <span class="px-3 py-1 rounded-full {{ $order->statusColor() }}" data-order-status-for="{{ $order->order_number }}">{{ ucfirst($order->status) }}</span>
        <span class="px-3 py-1 rounded-full {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}" data-payment-status-for="{{ $order->order_number }}">
            {{ $order->payment_status === 'paid' ? 'Paid' : 'Payment pending' }}
        </span>
    </div>

    @if($order->payment_method === 'card' && $order->payment_status !== 'paid')
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 text-sm rounded-lg px-4 py-3 mb-6 text-left">
            We're still confirming your payment with Stripe. This page will update automatically once it's confirmed — no need to refresh.
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6 text-left">
        <h2 class="font-semibold mb-3">Order Items</h2>
        <div class="space-y-2">
            @foreach($order->items as $item)
                <div class="flex justify-between text-sm">
                    <span>{{ $item->product_name }} × {{ $item->quantity }}</span>
                    <span>${{ number_format($item->subtotal, 2) }}</span>
                </div>
            @endforeach
        </div>
        <div class="border-t mt-4 pt-4 space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>${{ number_format($order->subtotal ?: $order->total, 2) }}</span></div>
            @if($order->coupon_code)
                <div class="flex justify-between text-green-600"><span>Discount ({{ $order->coupon_code }})</span><span>-${{ number_format($order->discount_amount, 2) }}</span></div>
            @endif
            <div class="flex justify-between"><span class="text-gray-500">Shipping</span><span>{{ $order->shipping_amount > 0 ? '$' . number_format($order->shipping_amount, 2) : 'Free' }}</span></div>
            @if($order->tax_amount > 0)
                <div class="flex justify-between"><span class="text-gray-500">Tax</span><span>${{ number_format($order->tax_amount, 2) }}</span></div>
            @endif
        </div>
        <div class="border-t mt-2 pt-4 flex justify-between font-bold">
            <span>Total</span>
            <span class="text-blue-600">${{ number_format($order->total, 2) }}</span>
        </div>
    </div>

    <a href="{{ route('shop.index') }}" class="inline-block mt-8 bg-blue-600 text-white px-6 py-3 rounded-lg font-medium">Continue Shopping</a>
</div>
@endsection