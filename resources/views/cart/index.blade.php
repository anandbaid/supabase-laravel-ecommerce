@extends('layouts.app')
@section('title', 'Your Cart - ShopEase')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Your Cart</h1>

    @if(empty($items))
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <p class="text-gray-400 mb-4">Your cart is empty.</p>
            <a href="{{ route('shop.index') }}" class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm">Continue Shopping</a>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm divide-y">
            @foreach($items as $item)
                <div class="flex items-center gap-4 p-4">
                    <img src="{{ $item['product']->imageUrl() }}" class="w-16 h-16 object-contain bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <a href="{{ route('shop.show', $item['product']->slug) }}" class="font-medium hover:text-blue-600">{{ $item['product']->name }}</a>
                        <div class="text-sm text-gray-500">${{ number_format($item['product']->finalPrice(), 2) }} each</div>
                    </div>
                    <form action="{{ route('cart.update', $item['product']) }}" method="POST" class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <input type="number" name="qty" value="{{ $item['qty'] }}" min="1" class="w-16 border rounded-lg px-2 py-1 text-sm" onchange="this.form.submit()">
                    </form>
                    <div class="w-24 text-right font-semibold">${{ number_format($item['subtotal'], 2) }}</div>
                    <form action="{{ route('cart.remove', $item['product']) }}" method="POST">
                        @csrf @method('DELETE')
                        <button class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="flex flex-col md:flex-row md:justify-end gap-4 mt-6">
            <div class="bg-white rounded-xl shadow-sm p-6 w-full max-w-sm">
                <h3 class="font-semibold text-sm mb-2">Have a coupon code?</h3>
                @if($coupon)
                    <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-lg px-3 py-2 text-sm">
                        <span class="text-green-700 font-medium">{{ $coupon->code }} applied</span>
                        <form action="{{ route('cart.coupon.remove') }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:underline text-xs">Remove</button>
                        </form>
                    </div>
                @else
                    <form action="{{ route('cart.coupon.apply') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="code" placeholder="Enter coupon code" class="flex-1 border rounded-lg px-3 py-2 text-sm uppercase">
                        <button class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">Apply</button>
                    </form>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 w-full max-w-sm">
                <div class="space-y-2 text-sm mb-4">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span>${{ number_format($subtotal, 2) }}</span>
                    </div>
                    @if($coupon)
                        <div class="flex justify-between text-green-600">
                            <span>Discount</span>
                            <span>-${{ number_format($discount, 2) }}</span>
                        </div>
                    @endif
                    @if($taxRate > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tax ({{ rtrim(rtrim(number_format($taxRate, 2), '0'), '.') }}%)</span>
                            <span>${{ number_format($taxAmount, 2) }}</span>
                        </div>
                    @endif
                </div>
                <div class="flex justify-between mb-4 border-t pt-3">
                    <span class="text-gray-500">Total</span>
                    <span class="text-xl font-bold text-blue-600">${{ number_format($total, 2) }}</span>
                </div>
                <a href="{{ route('checkout.index') }}" class="block text-center bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700">Proceed to Checkout</a>
            </div>
        </div>
    @endif
</div>
@endsection
