@extends('layouts.app')
@section('title', 'Your Cart - Let\'s Shop')

@section('content')
@php
    $itemCount = collect($items)->sum('qty');
    $money = fn ($n) => '$' . number_format($n, 2);
    $maxLine = \App\Http\Controllers\CartController::MAX_QTY_PER_ITEM;
@endphp
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-6">
        <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center"><i data-lucide="shopping-cart" class="w-6 h-6"></i></div>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Your Cart</h1>
            <p class="text-sm text-gray-500">{{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }} in your cart</p>
        </div>
    </div>

    @if(empty($items))
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-500 mx-auto flex items-center justify-center mb-4"><i data-lucide="shopping-bag" class="w-7 h-7"></i></div>
            <h2 class="font-semibold text-slate-900 mb-1">Your cart is empty</h2>
            <p class="text-gray-500 text-sm mb-5">Add something you like and it will show up here.</p>
            <a href="{{ route('shop.index') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium">Browse the shop</a>
        </div>
    @else
        <div class="grid lg:grid-cols-[1fr_380px] gap-6 items-start">
            {{-- Left column --}}
            <div class="space-y-5 min-w-0">
                @if($freeShippingRemaining > 0)
                    @php $progress = min(100, round($subtotal / $freeShippingThreshold * 100)); @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                        <div class="flex items-center gap-2 text-sm text-slate-700 mb-2">
                            <i data-lucide="truck" class="w-4 h-4 text-blue-600"></i>
                            Add <span class="font-semibold">{{ $money($freeShippingRemaining) }}</span> more for free shipping
                        </div>
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-blue-600 rounded-full" style="width: {{ $progress }}%"></div></div>
                    </div>
                @else
                    <div class="bg-green-50 border border-green-200 text-green-700 rounded-2xl p-4 text-sm flex items-center gap-2">
                        <i data-lucide="party-popper" class="w-4 h-4"></i> You've unlocked free shipping.
                    </div>
                @endif

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100">
                    @foreach($items as $item)
                        @php
                            $p = $item['product'];
                            $lineMax = max(1, min($maxLine, (int) $p->stock));
                        @endphp
                        <div class="p-5 flex gap-4 sm:gap-6">
                            <a href="{{ route('shop.show', $p->slug) }}" class="w-24 h-24 sm:w-32 sm:h-32 rounded-xl bg-gray-50 shrink-0 flex items-center justify-center overflow-hidden">
                                <img src="{{ $p->imageUrl() }}" alt="{{ $p->name }}" class="w-full h-full object-contain p-2">
                            </a>
                            <div class="flex-1 min-w-0 flex flex-col">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <a href="{{ route('shop.show', $p->slug) }}" class="font-semibold text-lg text-slate-900 hover:text-blue-600 block truncate">{{ $p->name }}</a>
                                        @if($p->stock > 0)
                                            <span class="inline-flex items-center gap-1 mt-1 text-xs text-green-700 bg-green-50 px-2 py-0.5 rounded-md"><i data-lucide="check" class="w-3 h-3"></i> In Stock</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 mt-1 text-xs text-red-700 bg-red-50 px-2 py-0.5 rounded-md">Out of stock</span>
                                        @endif
                                        <div class="text-sm text-gray-500 mt-2">{{ $money($p->finalPrice()) }} each</div>
                                    </div>
                                    <div class="font-bold text-lg text-slate-900">{{ $money($item['subtotal']) }}</div>
                                </div>

                                <div class="flex items-end justify-between mt-auto pt-3">
                                    <form action="{{ route('cart.update', $p) }}" method="POST" class="inline-flex items-center border border-gray-200 rounded-lg overflow-hidden">
                                        @csrf @method('PATCH')
                                        <button name="qty" value="{{ max(1, $item['qty'] - 1) }}" @disabled($item['qty'] <= 1) aria-label="Decrease quantity" class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-50 disabled:text-gray-300 disabled:hover:bg-transparent"><i data-lucide="minus" class="w-4 h-4"></i></button>
                                        <span class="w-12 text-center text-sm font-medium" aria-live="polite">{{ $item['qty'] }}</span>
                                        <button name="qty" value="{{ $item['qty'] + 1 }}" @disabled($item['qty'] >= $lineMax) aria-label="Increase quantity" class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-50 disabled:text-gray-300 disabled:hover:bg-transparent"><i data-lucide="plus" class="w-4 h-4"></i></button>
                                    </form>
                                    <form action="{{ route('cart.remove', $p) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button aria-label="Remove {{ $p->name }} from cart" class="w-10 h-10 rounded-lg bg-gray-50 text-gray-500 hover:bg-red-50 hover:text-red-600 flex items-center justify-center"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </form>
                                </div>
                                @if($item['qty'] >= $lineMax)
                                    <p class="text-xs text-gray-400 mt-2">Maximum {{ $lineMax }} per order for this item.</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Coupon --}}
                <div class="bg-blue-50/60 border border-blue-100 rounded-2xl p-5 flex flex-col md:flex-row md:items-center gap-4">
                    <div class="flex items-center gap-4 flex-1">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0"><i data-lucide="ticket" class="w-5 h-5"></i></div>
                        <div>
                            <div class="font-semibold text-slate-900">Have a coupon code?</div>
                            <div class="text-sm text-gray-500">Enter your coupon code to get a discount on your order.</div>
                        </div>
                    </div>
                    @if($coupon)
                        <div class="flex items-center justify-between gap-3 bg-green-50 border border-green-200 rounded-lg px-4 py-2.5 text-sm md:w-72">
                            <span class="text-green-700 font-medium">{{ $coupon->code }} applied</span>
                            <form action="{{ route('cart.coupon.remove') }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:underline text-xs">Remove</button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('cart.coupon.apply') }}" method="POST" class="flex gap-2 md:w-96">
                            @csrf
                            <input type="text" name="code" placeholder="Enter coupon code" aria-label="Coupon code" class="flex-1 min-w-0 border border-gray-200 rounded-lg px-3 py-2.5 text-sm uppercase placeholder:normal-case focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 rounded-lg text-sm font-medium">Apply</button>
                        </form>
                    @endif
                </div>

                <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 text-sm font-medium"><i data-lucide="arrow-left" class="w-4 h-4"></i> Continue Shopping</a>
            </div>

            {{-- Order summary --}}
            <aside class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:sticky lg:top-24">
                <h2 class="text-xl font-bold text-slate-900 mb-5">Order Summary</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Subtotal ({{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }})</dt><dd class="font-medium">{{ $money($subtotal) }}</dd></div>
                    @if($coupon)
                        <div class="flex justify-between text-green-600"><dt>Discount ({{ $coupon->code }})</dt><dd class="font-medium">-{{ $money($discount) }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-gray-500">Shipping</dt><dd class="font-medium">{{ $shipping > 0 ? $money($shipping) : 'Free' }}</dd></div>
                    @if($taxRate > 0)
                        <div class="flex justify-between"><dt class="text-gray-500">Tax ({{ rtrim(rtrim(number_format($taxRate, 2), '0'), '.') }}%)</dt><dd class="font-medium">{{ $money($taxAmount) }}</dd></div>
                    @endif
                </dl>
                <div class="flex justify-between items-baseline border-t border-gray-100 mt-4 pt-4">
                    <span class="text-lg font-bold text-slate-900">Total</span>
                    <span class="text-2xl font-bold text-blue-600">{{ $money($total) }}</span>
                </div>
                <a href="{{ route('checkout.index') }}" class="mt-5 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-3.5 rounded-lg font-semibold">
                    <i data-lucide="lock" class="w-4 h-4"></i> Proceed to Checkout
                </a>

                <div class="grid grid-cols-2 gap-3 mt-6 text-xs text-gray-600">
                    <div class="flex items-center gap-2"><i data-lucide="shield-check" class="w-5 h-5 text-gray-500 shrink-0"></i> Secure Checkout</div>
                    <div class="flex items-center gap-2"><i data-lucide="truck" class="w-5 h-5 text-gray-500 shrink-0"></i> Fast Delivery</div>
                    <div class="flex items-center gap-2"><i data-lucide="refresh-cw" class="w-5 h-5 text-gray-500 shrink-0"></i> Easy Returns</div>
                    <div class="flex items-center gap-2"><i data-lucide="headphones" class="w-5 h-5 text-gray-500 shrink-0"></i> 24/7 Support</div>
                </div>
            </aside>
        </div>
    @endif
</div>
@endsection
