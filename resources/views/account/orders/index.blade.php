@extends('layouts.account')

@section('account-content')
    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center"><i data-lucide="package" class="w-6 h-6"></i></div>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">My Orders</h1>
            <p class="text-sm text-gray-500">{{ $orders->total() }} {{ \Illuminate\Support\Str::plural('order', $orders->total()) }} placed</p>
        </div>
    </div>

    @if($orders->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-500 mx-auto flex items-center justify-center mb-4"><i data-lucide="package-search" class="w-7 h-7"></i></div>
            <h2 class="font-semibold text-slate-900 mb-1">No orders yet</h2>
            <p class="text-gray-500 text-sm mb-5">When you place an order, it will show up here.</p>
            <a href="{{ route('shop.index') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium">Start shopping</a>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100">
            @foreach($orders as $order)
                <a href="{{ route('account.orders.show', $order->order_number) }}" class="flex flex-wrap items-center gap-4 p-5 hover:bg-gray-50">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0"><i data-lucide="receipt" class="w-5 h-5"></i></div>
                    <div class="flex-1 min-w-[10rem]">
                        <div class="font-semibold text-slate-900">{{ $order->order_number }}</div>
                        <div class="text-xs text-gray-500">{{ $order->created_at->format('d M Y, g:i A') }} &middot; {{ $order->items_count }} {{ \Illuminate\Support\Str::plural('item', $order->items_count) }}</div>
                    </div>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $order->statusColor() }}">{{ ucfirst($order->status) }}</span>
                    @if($order->return_status)
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $order->returnStatusColor() }}">{{ $order->returnStatusLabel() }}</span>
                    @endif
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $order->paymentStatusColor() }}">
                        {{ $order->paymentStatusLabel() }}
                    </span>
                    <div class="font-bold text-slate-900 w-20 text-right">${{ number_format($order->total, 2) }}</div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300"></i>
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
@endsection
