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
            <div class="flex items-center gap-2 flex-wrap justify-end">
                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $order->statusColor() }}" data-order-status-for="{{ $order->order_number }}">{{ ucfirst($order->status) }}</span>
                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $order->paymentStatusColor() }}" data-payment-status-for="{{ $order->order_number }}">
                    {{ $order->paymentStatusLabel() }}
                </span>
                @if($order->return_status)
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $order->returnStatusColor() }}">{{ $order->returnStatusLabel() }}</span>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3 mt-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 mt-3">{{ session('error') }}</div>
        @endif

        @if(!in_array($order->status, ['cancelled']))
            @php
                $steps = ['pending' => 'Placed', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
                $stepKeys = array_keys($steps);
                $currentIndex = array_search($order->status, $stepKeys, true);
                $currentIndex = $currentIndex === false ? 0 : $currentIndex;
            @endphp
            <div class="mt-6 mb-2">
                <div class="flex items-center">
                    @foreach($steps as $key => $label)
                        @php $done = array_search($key, $stepKeys, true) <= $currentIndex; @endphp
                        <div class="flex-1 flex items-center {{ $loop->last ? 'flex-none' : '' }}">
                            <div class="flex flex-col items-center {{ $loop->last ? '' : 'flex-1' }}">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold {{ $done ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                                    @if($done)
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </div>
                                <span class="text-[11px] mt-1 {{ $done ? 'text-blue-600 font-medium' : 'text-gray-400' }}">{{ $label }}</span>
                            </div>
                            @if(!$loop->last)
                                <div class="h-0.5 flex-1 -mt-4 {{ array_search($key, $stepKeys, true) < $currentIndex ? 'bg-blue-600' : 'bg-gray-100' }}"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($order->cancelledByCustomer())
            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 mt-3 text-sm text-gray-600">
                Cancelled on {{ $order->cancelled_at->format('d M Y') }}{{ $order->cancellation_reason ? ' — "' . $order->cancellation_reason . '"' : '' }}
            </div>
        @endif

        @if($order->canBeCancelled())
            <div class="mt-4">
                <button type="button" onclick="document.getElementById('cancel-order-form').classList.toggle('hidden')" class="inline-flex items-center gap-1.5 text-sm text-red-600 border border-red-200 rounded-lg px-3 py-1.5 hover:bg-red-50">
                    <i data-lucide="x-circle" class="w-4 h-4"></i> Cancel Order
                </button>
                <form id="cancel-order-form" action="{{ route('account.orders.cancel', $order->order_number) }}" method="POST" class="hidden mt-3 bg-red-50 border border-red-100 rounded-lg p-4">
                    @csrf
                    <label class="text-sm font-medium">Why are you cancelling? <span class="text-gray-400 font-normal">— optional</span></label>
                    <textarea name="reason" rows="2" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Orders can only be cancelled before they ship. If you paid by card, you'll be refunded automatically.</p>
                    <div class="flex gap-2 mt-3">
                        <button class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700" onclick="return confirm('Cancel this order? This can\'t be undone.')">Confirm Cancellation</button>
                    </div>
                </form>
            </div>
        @elseif($order->canRequestReturn())
            <div class="mt-4">
                <button type="button" onclick="document.getElementById('return-order-form').classList.toggle('hidden')" class="inline-flex items-center gap-1.5 text-sm text-blue-600 border border-blue-200 rounded-lg px-3 py-1.5 hover:bg-blue-50">
                    <i data-lucide="undo-2" class="w-4 h-4"></i> Request Return
                </button>
                <p class="text-xs text-gray-400 mt-1.5">Eligible until {{ $order->returnWindowExpiresAt()->format('d M Y') }} (7 days from delivery).</p>
                <form id="return-order-form" action="{{ route('account.orders.return', $order->order_number) }}" method="POST" class="hidden mt-3 bg-blue-50 border border-blue-100 rounded-lg p-4">
                    @csrf
                    <label class="text-sm font-medium">Reason for return</label>
                    <textarea name="reason" rows="3" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm"></textarea>
                    <div class="flex gap-2 mt-3">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">Submit Return Request</button>
                    </div>
                </form>
            </div>
        @elseif($order->return_status === 'requested')
            <p class="text-sm text-gray-500 mt-4">Your return request is being reviewed. We'll update you here once it's decided.</p>
        @elseif($order->status === 'delivered' && !$order->return_status)
            <p class="text-xs text-gray-400 mt-4">The 7-day return window for this order has closed.</p>
        @endif

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
