@extends('layouts.app')
@section('title', "About Us - Let's Shop")

@section('content')
<div class="max-w-6xl mx-auto px-4 py-12">
    <div class="grid md:grid-cols-2 gap-10 items-center mb-12">
        <div>
            <h1 class="text-3xl font-bold mb-3">About Us</h1>
            <p class="text-gray-500 mb-3">Your Trusted Online Shopping Partner</p>
            <p class="text-gray-600 leading-relaxed">
                We are passionate about bringing you the best products at the best prices.
                Our mission is to make online shopping easy, affordable, and enjoyable for everyone —
                from everyday essentials to the things you didn't know you needed.
            </p>
        </div>
        <div class="bg-blue-50 rounded-2xl p-10 flex items-center justify-center">
            <i data-lucide="shopping-bag" class="w-28 h-28 text-blue-400"></i>
        </div>
    </div>

    <div class="grid sm:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <i data-lucide="badge-check" class="w-8 h-8 text-blue-600 mx-auto mb-3"></i>
            <h3 class="font-semibold mb-1">Best Quality</h3>
            <p class="text-sm text-gray-500">Top rated products, checked and verified.</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <i data-lucide="tag" class="w-8 h-8 text-blue-600 mx-auto mb-3"></i>
            <h3 class="font-semibold mb-1">Affordable Prices</h3>
            <p class="text-sm text-gray-500">Save more, shop more, every single day.</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <i data-lucide="smile" class="w-8 h-8 text-blue-600 mx-auto mb-3"></i>
            <h3 class="font-semibold mb-1">Happy Customers</h3>
            <p class="text-sm text-gray-500">Thousands of satisfied shoppers and counting.</p>
        </div>
    </div>
</div>
@endsection
