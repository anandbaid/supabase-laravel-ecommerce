@extends('layouts.account')

@section('account-content')
<div class="space-y-6">
    {{-- Profile card --}}
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-16 h-16 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
            <i data-lucide="user" class="w-8 h-8"></i>
        </div>
        <div>
            <div class="font-bold text-lg text-slate-900">{{ $user->name }}</div>
            <div class="text-sm text-gray-500">{{ $user->email }}</div>
            <div class="text-xs text-gray-400 mt-0.5">Member since {{ $user->created_at->format('M Y') }}</div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 text-center">
            <div class="text-xs text-gray-400 mb-1">Total Orders</div>
            <div class="text-2xl font-bold text-slate-900">{{ $ordersCount }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 text-center">
            <div class="text-xs text-gray-400 mb-1">Wishlist Items</div>
            <div class="text-2xl font-bold text-slate-900">{{ $wishlistCount }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 text-center">
            <div class="text-xs text-gray-400 mb-1">Loyalty Points</div>
            <div class="text-2xl font-bold text-slate-900">{{ $loyaltyPoints }}</div>
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-slate-900">Recent Orders</h2>
            <a href="{{ route('account.orders.index') }}" class="text-sm text-blue-600 hover:underline">View All</a>
        </div>

        @if($recentOrders->isEmpty())
            <div class="text-center py-10">
                <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-500 mx-auto flex items-center justify-center mb-3"><i data-lucide="package-search" class="w-6 h-6"></i></div>
                <p class="text-gray-500 text-sm mb-4">No orders yet.</p>
                <a href="{{ route('shop.index') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium">Start shopping</a>
            </div>
        @else
            <div class="divide-y">
                @foreach($recentOrders as $order)
                    @php $firstItem = $order->items->first(); @endphp
                    <a href="{{ route('account.orders.show', $order->order_number) }}" class="flex items-center gap-4 py-4 hover:bg-gray-50 -mx-2 px-2 rounded-lg">
                        <div class="w-11 h-11 rounded-lg bg-gray-50 border flex items-center justify-center overflow-hidden flex-shrink-0">
                            @if($firstItem?->product)
                                <img src="{{ $firstItem->product->imageUrl() }}" class="w-full h-full object-contain">
                            @else
                                <i data-lucide="package" class="w-5 h-5 text-gray-400"></i>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-slate-900 truncate">{{ $firstItem->product_name ?? 'Order ' . $order->order_number }}</div>
                            <div class="text-xs text-gray-400">Order #{{ $order->order_number }}</div>
                        </div>
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $order->statusColor() }}">{{ ucfirst($order->status) }}</span>
                        <div class="text-xs text-gray-400 w-20 text-right">{{ $order->created_at->format('M d, Y') }}</div>
                        <div class="font-bold text-slate-900 w-16 text-right">${{ number_format($order->total, 2) }}</div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
