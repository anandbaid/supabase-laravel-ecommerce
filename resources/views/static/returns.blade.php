@extends('layouts.app')
@section('title', "Return Policy - Let's Shop")

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold mb-2">Return Policy</h1>
    <p class="text-gray-500 mb-8">Hassle-Free Returns Policy</p>

    <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8 space-y-6">
        <div class="flex gap-4">
            <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 font-semibold">1</div>
            <div>
                <h2 class="font-semibold mb-1">Return Window</h2>
                <p class="text-gray-600 text-sm leading-relaxed">You can return most items within 30 days of delivery for a full refund or exchange.</p>
            </div>
        </div>
        <div class="flex gap-4">
            <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 font-semibold">2</div>
            <div>
                <h2 class="font-semibold mb-1">Condition</h2>
                <p class="text-gray-600 text-sm leading-relaxed">Items must be unused, in original packaging, and with tags attached.</p>
            </div>
        </div>
        <div class="flex gap-4">
            <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 font-semibold">3</div>
            <div>
                <h2 class="font-semibold mb-1">How to Initiate a Return</h2>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Go to <a href="{{ route('account.orders.index') }}" class="text-blue-600 hover:underline">My Orders</a>,
                    select the order, and choose "Request Return" — or
                    <a href="{{ route('static.contact') }}" class="text-blue-600 hover:underline">contact our support team</a> with your order number.
                </p>
            </div>
        </div>
        <div class="flex gap-4">
            <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 font-semibold">4</div>
            <div>
                <h2 class="font-semibold mb-1">Refunds</h2>
                <p class="text-gray-600 text-sm leading-relaxed">Once we receive and inspect your return, refunds are processed within 5–7 business days to your original payment method.</p>
            </div>
        </div>
        <p class="text-xs text-gray-400 pt-4 border-t">Some items (e.g. perishables, personal care, custom orders) may not be eligible for return — check the product page for details.</p>
    </div>
</div>
@endsection
