<div class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-4 relative">
    @if($product->discountPercent())
        <span class="absolute top-3 left-3 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full z-10">-{{ $product->discountPercent() }}%</span>
    @endif
    <button type="button" class="wishlist-btn absolute top-3 right-3 z-10 w-8 h-8 rounded-full bg-white/90 shadow flex items-center justify-center text-gray-400 hover:text-red-500 transition" title="Save to wishlist">
        <i data-lucide="heart" class="w-4 h-4"></i>
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
    <form action="{{ route('cart.add', $product) }}" method="POST" class="mt-3">
        @csrf
        <button class="w-full bg-blue-600 text-white text-sm py-2 rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2">
            <i data-lucide="shopping-cart" class="w-4 h-4"></i> Add to Cart
        </button>
    </form>
</div>

<script>
(function () {
    document.querySelectorAll('.wishlist-btn').forEach(function (btn) {
        if (btn.dataset.bound) return;
        btn.dataset.bound = '1';
        btn.addEventListener('click', function () {
            var active = btn.classList.toggle('text-red-500');
            var icon = btn.querySelector('i');
            icon.setAttribute('fill', active ? 'currentColor' : 'none');
        });
    });
    if (window.lucide) lucide.createIcons();
})();
</script>