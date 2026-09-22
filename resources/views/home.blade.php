@extends('layouts.app')
@section('title', 'ShopEase - Home')

@section('content')
<section class="relative bg-blue-50 overflow-hidden">
    <img src="{{ asset('images/hero-banner.png') }}" alt="Shop the latest products" class="absolute inset-0 w-full h-full object-cover object-right pointer-events-none select-none">

    <div class="relative max-w-7xl mx-auto px-4 py-20 md:py-28">
        <div class="max-w-xl">
            <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full mb-4">NEW ARRIVALS</span>
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight mb-4">Upgrade Your <span class="text-blue-600">Everyday Style</span></h1>
            <p class="text-gray-600 mb-6 max-w-md">Discover the latest trends in fashion, electronics, home essentials and more — all in one place.</p>
            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition">
                Shop Now <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
            <div class="flex flex-wrap gap-6 mt-8 text-sm text-gray-600">
                <div class="flex items-start gap-2">
                    <i data-lucide="truck" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                    <div>Free Shipping<br><span class="text-xs text-gray-400">On orders over $50</span></div>
                </div>
                <div class="flex items-start gap-2">
                    <i data-lucide="shield-check" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                    <div>Secure Payment<br><span class="text-xs text-gray-400">100% protected</span></div>
                </div>
                <div class="flex items-start gap-2">
                    <i data-lucide="rotate-ccw" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                    <div>Easy Returns<br><span class="text-xs text-gray-400">Within 7 days</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="absolute top-10 right-6 md:top-14 md:right-16 bg-blue-600 text-white text-sm font-semibold px-4 py-3 rounded-2xl rounded-br-sm shadow-lg rotate-3 leading-snug hidden sm:block">
        Best Deals<br><span class="text-base">Up to 50% OFF</span>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-12">
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
        @foreach($categories as $cat)
            <a href="{{ route('shop.index', ['category' => $cat->slug]) }}" class="group bg-white hover:bg-blue-50 border border-gray-100 rounded-xl p-5 text-center transition shadow-sm">
                <div class="w-16 h-16 mx-auto mb-3 rounded-lg overflow-hidden bg-gray-50">
                    <img src="{{ $cat->imageUrl() }}" class="w-full h-full object-cover" alt="{{ $cat->name }}" onerror="this.style.display='none'">
                </div>
                <div class="text-sm font-medium">{{ $cat->name }}</div>
            </a>
        @endforeach
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">Featured Products</h2>
        <a href="{{ route('shop.index') }}" class="text-blue-600 text-sm font-medium flex items-center gap-1">View All <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-5">
        @forelse($featured as $product)
            @include('shop.partials.card', ['product' => $product])
        @empty
            <p class="text-gray-400 col-span-full">No featured products yet.</p>
        @endforelse
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">Latest Products</h2>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        @forelse($latest as $product)
            @include('shop.partials.card', ['product' => $product])
        @empty
            <p class="text-gray-400 col-span-full">No products yet.</p>
        @endforelse
    </div>
</section>

<x-recently-viewed />
@endsection