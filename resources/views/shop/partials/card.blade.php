@php $inWishlist = (isset($wishlistIds) ? $wishlistIds : collect())->contains($product->id); @endphp
<div class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-4 relative">
    @if($product->discountPercent())
        <span class="absolute top-3 left-3 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full z-10">-{{ $product->discountPercent() }}%</span>
    @endif
    <button type="button" data-wishlist-btn data-product-id="{{ $product->id }}" aria-pressed="{{ $inWishlist ? 'true' : 'false' }}"
            onclick="window.toggleWishlist({{ $product->id }}, this)"
            class="absolute top-3 right-3 z-10 w-8 h-8 rounded-full bg-white/90 shadow flex items-center justify-center transition {{ $inWishlist ? 'text-red-500' : 'text-gray-400 hover:text-red-500' }}"
            title="{{ $inWishlist ? 'Remove from wishlist' : 'Save to wishlist' }}">
        <i data-lucide="heart" class="w-4 h-4" fill="{{ $inWishlist ? 'currentColor' : 'none' }}"></i>
    </button>
    <a href="{{ route('shop.show', $product->slug) }}">
        <div class="h-32 flex items-center justify-center mb-3">
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="max-h-32 object-contain">
        </div>
        <div class="text-sm font-medium text-gray-800 line-clamp-2">{{ $product->name }}</div>
    </a>
    <div class="mt-2 flex items-center gap-2">
        <span class="text-blue-600 font-semibold">${{ number_format($product->finalPrice(), 2) }}</span>
        @if($product->discount_price)
            <span class="text-gray-400 text-xs line-through">${{ number_format($product->price, 2) }}</span>
        @endif
    </div>
    <button type="button" onclick="window.addToCart({{ $product->id }}, 1, this)"
            class="mt-3 w-full bg-blue-600 text-white text-sm py-2 rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2 transition disabled:cursor-not-allowed">
        <i data-lucide="shopping-cart" class="w-4 h-4"></i> Add to Cart
    </button>
</div>
