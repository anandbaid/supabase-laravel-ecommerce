@extends('layouts.app')
@section('title', $product->name . ' - ShopEase')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid md:grid-cols-2 gap-10 bg-white rounded-xl shadow-sm p-8">
        <div>
            @php $images = $product->galleryUrls(); @endphp

            {{-- Main viewer with hover-to-zoom --}}
            <div
                id="product-zoom-viewer"
                class="relative bg-gray-50 rounded-xl overflow-hidden cursor-zoom-in select-none"
                style="aspect-ratio: 1 / 1;"
            >
                <img
                    id="product-zoom-image"
                    src="{{ $images[0] }}"
                    alt="{{ $product->name }}"
                    class="w-full h-full object-contain p-6 transition-transform duration-150 ease-out"
                    style="transform-origin: center center;"
                >
                <div id="product-zoom-hint" class="absolute bottom-2 right-2 text-[10px] bg-black/50 text-white px-2 py-1 rounded-full pointer-events-none opacity-80">
                    Hover to zoom
                </div>
            </div>

            @if(count($images) > 1)
                {{-- Thumbnail slider --}}
                <div class="flex items-center gap-2 mt-4">
                    <button type="button" id="product-slider-prev" aria-label="Previous image" class="shrink-0 w-8 h-8 rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50">‹</button>

                    <div class="flex gap-2 overflow-x-auto flex-1 pb-1" id="product-thumbnails">
                        @foreach($images as $i => $url)
                            <button
                                type="button"
                                class="product-thumb shrink-0 w-16 h-16 rounded-lg border-2 overflow-hidden {{ $i === 0 ? 'border-blue-600' : 'border-transparent' }}"
                                data-image-url="{{ $url }}"
                                data-index="{{ $i }}"
                                aria-label="View image {{ $i + 1 }}"
                            >
                                <img src="{{ $url }}" alt="{{ $product->name }} thumbnail {{ $i + 1 }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>

                    <button type="button" id="product-slider-next" aria-label="Next image" class="shrink-0 w-8 h-8 rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50">›</button>
                </div>
            @endif
        </div>
        <div>
            <p class="text-sm text-blue-600 font-medium mb-1">{{ $product->category->name }}</p>
            <h1 class="text-2xl font-bold mb-3">{{ $product->name }}</h1>
            <div class="flex items-center gap-3 mb-4">
                <span class="text-2xl text-blue-600 font-bold">${{ number_format($product->finalPrice(), 2) }}</span>
                @if($product->discount_price)
                    <span class="text-gray-400 line-through">${{ number_format($product->price, 2) }}</span>
                    <span class="bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded-full">-{{ $product->discountPercent() }}%</span>
                @endif
            </div>
            <p class="text-gray-600 mb-6">{{ $product->description ?: 'No description available.' }}</p>

            <p class="text-sm mb-4 {{ $product->stock > 0 ? 'text-green-600' : 'text-red-600' }}" data-stock-for="{{ $product->id }}-label">
                <span data-stock-for="{{ $product->id }}">{{ $product->stock }}</span> <span data-in-stock-for="{{ $product->id }}">{{ $product->stock > 0 ? 'in stock' : 'Out of stock' }}</span>
            </p>

            <form action="{{ route('cart.add', $product) }}" method="POST" class="flex items-center gap-3">
                @csrf
                <input type="number" name="qty" value="1" min="1" max="{{ $product->stock }}" class="w-20 border rounded-lg px-3 py-2 text-sm">
                <button {{ $product->stock <= 0 ? 'disabled' : '' }} class="bg-blue-600 disabled:bg-gray-300 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-blue-700 flex items-center gap-2">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i> Add to Cart
                </button>
            </form>

            <div class="mt-6 text-xs text-gray-400">SKU: {{ $product->sku }}</div>
        </div>
    </div>

    @if($related->count())
        <div class="mt-12">
            <h2 class="text-xl font-bold mb-5">Related Products</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @foreach($related as $r)
                    @include('shop.partials.card', ['product' => $r])
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
    (function () {
        var images = @json($product->galleryUrls());
        var currentIndex = 0;

        var viewer = document.getElementById('product-zoom-viewer');
        var image = document.getElementById('product-zoom-image');
        var thumbButtons = document.querySelectorAll('.product-thumb');
        var prevBtn = document.getElementById('product-slider-prev');
        var nextBtn = document.getElementById('product-slider-next');

        if (!viewer || !image) return;

        function setActiveThumb(index) {
            thumbButtons.forEach(function (btn) {
                var isActive = parseInt(btn.dataset.index, 10) === index;
                btn.classList.toggle('border-blue-600', isActive);
                btn.classList.toggle('border-transparent', !isActive);
            });
        }

        function showImage(index) {
            if (!images.length) return;
            currentIndex = ((index % images.length) + images.length) % images.length;
            image.src = images[currentIndex];
            setActiveThumb(currentIndex);
        }

        thumbButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                showImage(parseInt(btn.dataset.index, 10));
            });
        });

        if (prevBtn) prevBtn.addEventListener('click', function () { showImage(currentIndex - 1); });
        if (nextBtn) nextBtn.addEventListener('click', function () { showImage(currentIndex + 1); });

        // Hover-to-zoom: scale the image and shift its transform-origin to
        // follow the cursor, so the area under the pointer magnifies in place.
        var ZOOM_SCALE = 2;
        var isTouchDevice = window.matchMedia('(hover: none)').matches;

        if (!isTouchDevice) {
            viewer.addEventListener('mousemove', function (e) {
                var rect = viewer.getBoundingClientRect();
                var xPercent = ((e.clientX - rect.left) / rect.width) * 100;
                var yPercent = ((e.clientY - rect.top) / rect.height) * 100;

                image.style.transformOrigin = xPercent + '% ' + yPercent + '%';
                image.style.transform = 'scale(' + ZOOM_SCALE + ')';
            });

            viewer.addEventListener('mouseleave', function () {
                image.style.transform = 'scale(1)';
                image.style.transformOrigin = 'center center';
            });
        }
    })();
</script>
@endsection