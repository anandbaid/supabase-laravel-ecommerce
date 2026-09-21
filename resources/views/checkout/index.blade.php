@extends('layouts.app')
@section('title', 'Checkout - ShopEase')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10 grid md:grid-cols-3 gap-8">
    <form action="{{ route('checkout.store') }}" method="POST" class="md:col-span-2 bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf

        @if($addresses->isNotEmpty())
            <h2 class="text-lg font-bold mb-2">Deliver To</h2>
            <div class="space-y-2">
                @foreach($addresses as $address)
                    <label class="flex items-start gap-3 border rounded-lg p-3 cursor-pointer has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="address_id" value="{{ $address->id }}" class="mt-1" @checked($loop->first || $address->is_default)>
                        <div class="text-sm">
                            <span class="font-semibold">{{ $address->label }}</span>
                            @if($address->is_default)<span class="text-xs text-blue-600 ml-1">(Default)</span>@endif
                            <div class="text-gray-600">{{ $address->full_name }}, {{ $address->line1 }}{{ $address->line2 ? ', ' . $address->line2 : '' }}, {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}, {{ $address->country }}</div>
                            <div class="text-gray-400">Phone: {{ $address->phone }}</div>
                        </div>
                    </label>
                @endforeach
                <label class="flex items-center gap-2 text-sm mt-2">
                    <input type="radio" name="address_id" value="" id="new-address-radio"> Use a different address for this order
                </label>
            </div>
            <a href="{{ route('addresses.create') }}" class="inline-block text-sm text-blue-600 hover:underline">+ Add a new address</a>
        @endif

        <div id="new-address-fields" class="space-y-4 {{ $addresses->isNotEmpty() ? 'hidden' : '' }}">
            <h2 class="text-lg font-bold mb-2">Shipping Details</h2>
            <div>
                <label class="text-sm font-medium">Full Name</label>
                <input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()->name ?? '') }}" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Shipping Address</label>
                <textarea name="shipping_address" rows="3" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">{{ old('shipping_address') }}</textarea>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 pt-2">
            <div>
                <label class="text-sm font-medium">Email</label>
                <input type="email" name="customer_email" value="{{ old('customer_email', auth()->user()->email ?? '') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Phone</label>
                <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
            </div>
        </div>

        <div>
            <label class="text-sm font-medium block mb-2">Payment Method</label>
            <label class="flex items-center gap-2 mb-2 text-sm"><input type="radio" name="payment_method" value="cod" checked> Cash on Delivery</label>
            <label class="flex items-center gap-2 text-sm"><input type="radio" name="payment_method" value="card"> Credit / Debit Card (via Stripe)</label>
            <p class="text-xs text-gray-400 mt-2">Choosing card payment will take you to Stripe's secure checkout page to complete payment.</p>
        </div>
        <button class="w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 mt-4">Place Order</button>
    </form>

    <div class="bg-white rounded-xl shadow-sm p-6 h-fit">
        <h2 class="text-lg font-bold mb-4">Order Summary</h2>
        <div class="space-y-3 mb-4">
            @foreach($items as $item)
                <div class="flex justify-between text-sm">
                    <span>{{ $item['product']->name }} × {{ $item['qty'] }}</span>
                    <span class="font-medium">${{ number_format($item['subtotal'], 2) }}</span>
                </div>
            @endforeach
        </div>
        <div class="border-t pt-3 space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Subtotal</span>
                <span>${{ number_format($subtotal, 2) }}</span>
            </div>
            @if($coupon)
                <div class="flex justify-between text-green-600">
                    <span>Discount ({{ $coupon->code }})</span>
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
        <div class="border-t pt-4 mt-2 flex justify-between font-bold">
            <span>Total</span>
            <span class="text-blue-600">${{ number_format($total, 2) }}</span>
        </div>
    </div>
</div>

<script>
    var newAddressFields = document.getElementById('new-address-fields');
    document.querySelectorAll('input[name="address_id"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (newAddressFields) {
                newAddressFields.classList.toggle('hidden', this.id !== 'new-address-radio');
            }
        });
    });
</script>
@endsection
