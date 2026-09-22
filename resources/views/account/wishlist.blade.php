@extends('layouts.app')
@section('title', "My Wishlist - Let's Shop")

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center"><i data-lucide="heart" class="w-6 h-6" fill="currentColor"></i></div>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">My Wishlist</h1>
            <p class="text-sm text-gray-500">{{ $products->total() }} {{ \Illuminate\Support\Str::plural('item', $products->total()) }} saved</p>
        </div>
    </div>

    @if($products->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-red-50 text-red-400 mx-auto flex items-center justify-center mb-4"><i data-lucide="heart" class="w-7 h-7"></i></div>
            <h2 class="font-semibold text-slate-900 mb-1">Your wishlist is empty</h2>
            <p class="text-gray-500 text-sm mb-5">Tap the heart on any product to save it here for later.</p>
            <a href="{{ route('shop.index') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium">Browse the shop</a>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
            @foreach($products as $product)
                @include('shop.partials.card', ['product' => $product])
            @endforeach
        </div>
        <div class="mt-6">{{ $products->links() }}</div>
    @endif
</div>
@endsection
