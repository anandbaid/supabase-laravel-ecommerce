@extends('layouts.app')
@section('title', 'Checkout - Let\'s Shop')

@section('content')
@php
    $money = fn ($n) => '$' . number_format($n, 2);
    $countries = ['India', 'United States', 'United Kingdom', 'Canada', 'Australia', 'Bangladesh', 'Nepal', 'Singapore', 'United Arab Emirates', 'Other'];

    // Pre-fill billing from the account (name/email) and the user's default saved address.
    $nameParts = explode(' ', trim(auth()->user()->name ?? ''), 2);
    $sameChecked = old('same_as_billing', '1') == '1';
    $newShipChecked = $addresses->isEmpty() || old('shipping_line1');
    $selectedAddressId = $newShipChecked ? null : (old('address_id') ?: ($prefill->id ?? null));
    $itemCount = collect($items)->sum('qty');
@endphp
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center gap-3 mb-2">
        <a href="{{ route('cart.index') }}" aria-label="Back to cart" class="w-9 h-9 rounded-full border border-gray-200 bg-white text-gray-600 hover:text-blue-600 flex items-center justify-center"><i data-lucide="arrow-left" class="w-4 h-4"></i></a>
        <h1 class="text-2xl font-bold text-slate-900">Checkout</h1>
    </div>
    <p class="text-sm text-gray-500 mb-6 ml-12">Complete your order and get your products delivered!</p>

    {{-- Progress --}}
    <ol class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-4 mb-6 flex items-center gap-3 text-sm" id="checkout-steps" aria-label="Checkout progress">
        <li class="flex items-center gap-2 font-medium text-blue-600" data-step="1"><span class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs">1</span> Shipping</li>
        <span class="flex-1 h-px bg-gray-200" aria-hidden="true"></span>
        <li class="flex items-center gap-2 font-medium text-gray-500" data-step="2"><span class="w-7 h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-xs">2</span> Payment</li>
        <span class="flex-1 h-px bg-gray-200" aria-hidden="true"></span>
        <li class="flex items-center gap-2 font-medium text-gray-400" data-step="3"><span class="w-7 h-7 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-xs">3</span> Confirmation</li>
    </ol>

    <div class="grid lg:grid-cols-[1fr_400px] gap-6 items-start">
        {{-- ============ Form ============ --}}
        <form id="checkout-form" action="{{ route('checkout.store') }}" method="POST" class="space-y-5 min-w-0" novalidate>
            @csrf

            {{-- 1. Billing --}}
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" id="section-shipping">
                <h2 class="flex items-center gap-3 font-bold text-slate-900 mb-5"><span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center">1</span> Billing Details</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <x-form-field name="first_name" label="First Name" :value="$nameParts[0] ?? ''" required autocomplete="given-name" />
                    <x-form-field name="last_name" label="Last Name" :value="$nameParts[1] ?? ''" required autocomplete="family-name" />
                    <x-form-field name="customer_email" label="Email Address" type="email" :value="auth()->user()->email ?? ''" required autocomplete="email" />
                    <x-form-field name="customer_phone" label="Phone Number" type="tel" :value="$prefill->phone ?? ''" placeholder="+91 98765 43210" required autocomplete="tel" />
                    <x-form-field name="company_name" label="Company Name" optional placeholder="Enter company name" class="sm:col-span-2" autocomplete="organization" />

                    <div>
                        <label for="billing_country" class="block text-sm font-medium text-gray-700 mb-1">Country <span class="text-red-500" aria-hidden="true">*</span></label>
                        <select id="billing_country" name="billing_country" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            @foreach($countries as $c)
                                <option value="{{ $c }}" @selected(old('billing_country', $prefill->country ?? 'India') === $c)>{{ $c }}</option>
                            @endforeach
                        </select>
                        @error('billing_country')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <x-form-field name="billing_line1" label="Street Address" :value="$prefill->line1 ?? ''" required autocomplete="address-line1" />
                    <x-form-field name="billing_line2" label="Apartment, Suite, etc." :value="$prefill->line2 ?? ''" optional placeholder="Flat, floor, building (optional)" class="sm:col-span-2" autocomplete="address-line2" />
                    <x-form-field name="billing_city" label="City" :value="$prefill->city ?? ''" required autocomplete="address-level2" />
                    <div class="grid grid-cols-2 gap-4">
                        <x-form-field name="billing_state" label="State" :value="$prefill->state ?? ''" required autocomplete="address-level1" />
                        <x-form-field name="billing_postal_code" label="ZIP Code" :value="$prefill->postal_code ?? ''" required autocomplete="postal-code" />
                    </div>
                </div>
            </section>

            {{-- 2. Shipping --}}
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="flex items-center gap-3 font-bold text-slate-900 mb-4"><span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center">2</span> Shipping Address</h2>

                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer w-fit">
                    <input type="hidden" name="same_as_billing" value="0">
                    <input type="checkbox" id="same-as-billing" name="same_as_billing" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" @checked($sameChecked)>
                    Same as billing address
                </label>

                <div id="shipping-fields" class="mt-5 space-y-4 {{ $sameChecked ? 'hidden' : '' }}">
                    @if($addresses->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($addresses as $address)
                                <label class="flex items-start gap-3 border border-gray-200 rounded-lg p-3 cursor-pointer has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                                    <input type="radio" name="address_id" value="{{ $address->id }}" class="mt-1 text-blue-600 focus:ring-blue-500" @checked($selectedAddressId == $address->id)>
                                    <div class="text-sm">
                                        <span class="font-semibold">{{ $address->label }}</span>
                                        @if($address->is_default)<span class="text-xs text-blue-600 ml-1">(Default)</span>@endif
                                        <div class="text-gray-600">{{ $address->full_name }}, {{ $address->line1 }}{{ $address->line2 ? ', ' . $address->line2 : '' }}, {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}, {{ $address->country }}</div>
                                        <div class="text-gray-400">Phone: {{ $address->phone }}</div>
                                    </div>
                                </label>
                            @endforeach
                            <label class="flex items-center gap-3 border border-gray-200 rounded-lg p-3 cursor-pointer text-sm has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                                <input type="radio" name="address_id" value="" id="new-address-radio" class="text-blue-600 focus:ring-blue-500" @checked($newShipChecked)>
                                Ship to a different address
                            </label>
                            <a href="{{ route('addresses.create') }}" class="inline-block text-sm text-blue-600 hover:underline">+ Save a new address to your account</a>
                        </div>
                    @endif

                    <div id="new-address-fields" class="grid sm:grid-cols-2 gap-4 {{ $newShipChecked ? '' : 'hidden' }}">
                        <x-form-field name="shipping_first_name" label="First Name" :value="$nameParts[0] ?? ''" star />
                        <x-form-field name="shipping_last_name" label="Last Name" :value="$nameParts[1] ?? ''" star />
                        <x-form-field name="shipping_phone" label="Phone Number" type="tel" star />
                        <div>
                            <label for="shipping_country" class="block text-sm font-medium text-gray-700 mb-1">Country <span class="text-red-500" aria-hidden="true">*</span></label>
                            <select id="shipping_country" name="shipping_country" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                @foreach($countries as $c)
                                    <option value="{{ $c }}" @selected(old('shipping_country', 'India') === $c)>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-form-field name="shipping_line1" label="Street Address" star class="sm:col-span-2" />
                        <x-form-field name="shipping_line2" label="Apartment, Suite, etc." optional placeholder="Flat, floor, building (optional)" class="sm:col-span-2" />
                        <x-form-field name="shipping_city" label="City" star />
                        <div class="grid grid-cols-2 gap-4">
                            <x-form-field name="shipping_state" label="State" star />
                            <x-form-field name="shipping_postal_code" label="ZIP Code" star />
                        </div>
                    </div>
                </div>
            </section>

            {{-- 3. Notes --}}
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="flex items-center gap-3 font-bold text-slate-900 mb-4"><span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center">3</span> Order Notes <span class="text-gray-400 font-normal text-sm">(Optional)</span></h2>
                <textarea name="notes" id="order-notes" rows="3" maxlength="500" placeholder="Special delivery instructions, gift message, etc." aria-label="Order notes"
                          class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">{{ old('notes') }}</textarea>
                <div class="text-right text-xs text-gray-400 mt-1"><span id="notes-count">{{ strlen(old('notes', '')) }}</span>/500</div>
            </section>

            {{-- 4. Payment --}}
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" id="section-payment">
                <h2 class="flex items-center gap-3 font-bold text-slate-900 mb-4"><span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center">4</span> Payment Method</h2>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 border border-gray-200 rounded-lg p-4 cursor-pointer has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="payment_method" value="cod" class="text-blue-600 focus:ring-blue-500" @checked(old('payment_method', 'cod') === 'cod')>
                        <div class="flex-1"><div class="text-sm font-medium text-slate-900">Cash on Delivery</div><div class="text-xs text-gray-500">Pay when your order arrives at your doorstep.</div></div>
                        <i data-lucide="banknote" class="w-6 h-6 text-blue-600"></i>
                    </label>
                    <label class="flex items-center gap-3 border border-gray-200 rounded-lg p-4 cursor-pointer has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="payment_method" value="card" class="text-blue-600 focus:ring-blue-500" @checked(old('payment_method') === 'card')>
                        <div class="flex-1"><div class="text-sm font-medium text-slate-900">Credit / Debit Card <span class="text-gray-400 font-normal">(via Stripe)</span></div><div class="text-xs text-gray-500">You'll be taken to Stripe's secure page to complete payment.</div></div>
                        <i data-lucide="credit-card" class="w-6 h-6 text-blue-600"></i>
                    </label>
                </div>
            </section>
        </form>

        {{-- ============ Summary ============ --}}
        <aside class="space-y-5 lg:sticky lg:top-24">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-slate-900">Order Summary</h2>
                    <a href="{{ route('cart.index') }}" class="text-xs text-blue-600 hover:underline">Edit cart</a>
                </div>

                <ul class="space-y-3 mb-4">
                    @foreach($items as $item)
                        <li class="flex items-center gap-3">
                            <img src="{{ $item['product']->imageUrl() }}" alt="" class="w-14 h-14 rounded-lg bg-gray-50 object-contain p-1 shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-slate-900 truncate">{{ $item['product']->name }}</div>
                                <div class="text-xs text-gray-500">Qty: {{ $item['qty'] }}</div>
                            </div>
                            <div class="text-sm font-semibold">{{ $money($item['subtotal']) }}</div>
                        </li>
                    @endforeach
                </ul>

                <dl class="border-t border-gray-100 pt-4 space-y-2.5 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Product Total</dt><dd>{{ $money($subtotal) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Shipping</dt><dd>{{ $shipping > 0 ? $money($shipping) : 'Free' }}</dd></div>
                    @if($taxRate > 0)
                        <div class="flex justify-between"><dt class="text-gray-500">Tax ({{ rtrim(rtrim(number_format($taxRate, 2), '0'), '.') }}%)</dt><dd>{{ $money($taxAmount) }}</dd></div>
                    @endif
                    @if($coupon)
                        <div class="flex justify-between text-green-600"><dt>Discount ({{ $coupon->code }})</dt><dd>-{{ $money($discount) }}</dd></div>
                    @endif
                </dl>
                <div class="flex justify-between items-baseline border-t border-gray-100 mt-4 pt-4">
                    <span class="font-bold text-slate-900">Grand Total</span>
                    <span class="text-2xl font-bold text-blue-600">{{ $money($total) }}</span>
                </div>

                {{-- Coupon (own form — can't nest inside the checkout form) --}}
                @if($coupon)
                    <div class="mt-4 flex items-center justify-between bg-green-50 border border-green-200 rounded-lg px-3 py-2 text-sm">
                        <span class="text-green-700 font-medium">{{ $coupon->code }} applied</span>
                        <form action="{{ route('cart.coupon.remove') }}" method="POST">@csrf @method('DELETE')<button class="text-red-500 hover:underline text-xs">Remove</button></form>
                    </div>
                @else
                    <form action="{{ route('cart.coupon.apply') }}" method="POST" class="mt-4 flex gap-2">
                        @csrf
                        <input type="text" name="code" placeholder="Enter coupon code" aria-label="Coupon code" class="flex-1 min-w-0 border border-gray-200 rounded-lg px-3 py-2 text-sm uppercase placeholder:normal-case focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 rounded-lg text-sm font-medium">Apply</button>
                    </form>
                @endif

                <button type="submit" form="checkout-form" id="place-order" class="mt-5 w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-70 text-white py-3.5 rounded-lg font-semibold">
                    <i data-lucide="lock" class="w-4 h-4"></i> <span>Place Order</span>
                </button>
                <p class="text-xs text-gray-400 text-center mt-3">By placing this order, you confirm the details above are correct.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-slate-900 mb-3">Your order includes</h3>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start gap-3"><i data-lucide="shield-check" class="w-5 h-5 text-blue-600 shrink-0"></i><span><span class="font-medium text-slate-900 block">Secure Checkout</span><span class="text-xs text-gray-500">Your information is safe with us.</span></span></li>
                    <li class="flex items-start gap-3"><i data-lucide="refresh-cw" class="w-5 h-5 text-blue-600 shrink-0"></i><span><span class="font-medium text-slate-900 block">Easy Returns</span><span class="text-xs text-gray-500">Hassle-free returns within 7 days.</span></span></li>
                    <li class="flex items-start gap-3"><i data-lucide="truck" class="w-5 h-5 text-blue-600 shrink-0"></i><span><span class="font-medium text-slate-900 block">Fast Delivery</span><span class="text-xs text-gray-500">Free shipping over {{ $money($freeShippingThreshold) }}.</span></span></li>
                </ul>
            </div>
        </aside>
    </div>
</div>

<script>
    (function () {
        var same = document.getElementById('same-as-billing');
        var shipFields = document.getElementById('shipping-fields');
        var newFields = document.getElementById('new-address-fields');
        var radios = document.querySelectorAll('input[name="address_id"]');

        function syncShipping() {
            shipFields.classList.toggle('hidden', same.checked);
            if (radios.length) {
                var useNew = document.getElementById('new-address-radio').checked;
                newFields.classList.toggle('hidden', !useNew);
            }
        }
        same.addEventListener('change', syncShipping);
        radios.forEach(function (r) { r.addEventListener('change', syncShipping); });
        syncShipping();

        // Order-notes character counter
        var notes = document.getElementById('order-notes');
        var count = document.getElementById('notes-count');
        notes.addEventListener('input', function () { count.textContent = notes.value.length; });

        // Progress: highlight "Payment" once the payment section is in view
        var steps = document.querySelectorAll('#checkout-steps [data-step="2"]');
        var pay = document.getElementById('section-payment');
        if ('IntersectionObserver' in window && pay && steps.length) {
            new IntersectionObserver(function (entries) {
                entries.forEach(function (e) {
                    var li = steps[0], dot = li.querySelector('span');
                    li.classList.toggle('text-blue-600', e.isIntersecting);
                    li.classList.toggle('text-gray-500', !e.isIntersecting);
                    dot.classList.toggle('bg-blue-600', e.isIntersecting);
                    dot.classList.toggle('text-white', e.isIntersecting);
                    dot.classList.toggle('bg-gray-100', !e.isIntersecting);
                    dot.classList.toggle('text-gray-500', !e.isIntersecting);
                });
            }, { threshold: 0.4 }).observe(pay);
        }

        // Validate on submit (form is novalidate so hidden fields never block), then prevent double-submits
        var form = document.getElementById('checkout-form');
        var btn = document.getElementById('place-order');
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                form.reportValidity();
                return;
            }
            btn.disabled = true;
            btn.querySelector('span').textContent = 'Placing order…';
        });
        // Coming back via the browser's Back button (e.g. from Stripe): re-enable the button.
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) { btn.disabled = false; btn.querySelector('span').textContent = 'Place Order'; }
        });
    })();
</script>
@endsection
