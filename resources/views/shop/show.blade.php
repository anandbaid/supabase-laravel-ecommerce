@extends('layouts.app')
@section('title', $product->name . ' - Let\'s Shop')

@section('content')
@php
    $images = $product->galleryUrls();
    $maxQty = max(1, min(\App\Http\Controllers\CartController::MAX_QTY_PER_ITEM, (int) $product->stock));
    $inStock = $product->stock > 0;
    $faqs = [
        ['How much does shipping cost?', 'Shipping is $' . number_format(\App\Models\Setting::shippingFee(), 2) . ' per order, and free when your subtotal is $' . rtrim(rtrim(number_format(\App\Models\Setting::freeShippingThreshold(), 2), '0'), '.') . ' or more.'],
        ['Can I return this product?', 'Yes, returns are accepted within 7 days of delivery.'],
        ['Which payment methods do you accept?', 'Cash on delivery and credit / debit card, processed securely by Stripe.'],
        ['Who can write a review?', 'Any logged-in customer. Reviews from people who bought the product show a Verified buyer badge.'],
    ];
@endphp
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Breadcrumb --}}
    <nav class="text-xs text-gray-500 mb-4 flex flex-wrap items-center gap-1.5" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('shop.index') }}" class="hover:text-blue-600">Shop</a>
        @if($product->category)
            <span aria-hidden="true">/</span>
            <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" class="hover:text-blue-600">{{ $product->category->name }}</a>
        @endif
        <span aria-hidden="true">/</span>
        <span class="text-gray-700">{{ $product->name }}</span>
    </nav>

    <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 bg-white rounded-2xl shadow-sm border border-gray-100 p-5 md:p-8">
        {{-- Gallery --}}
        <div class="flex flex-col-reverse md:flex-row gap-4">
            @if(count($images) > 1)
                <div class="flex md:flex-col items-center gap-2 md:w-16 shrink-0">
                    <div class="flex md:flex-col gap-2 overflow-x-auto md:overflow-y-auto md:max-h-[420px] flex-1 md:flex-none pb-1" id="product-thumbnails">
                        @foreach($images as $i => $url)
                            <button type="button"
                                    class="product-thumb shrink-0 w-16 h-16 rounded-lg border-2 overflow-hidden bg-gray-50 {{ $i === 0 ? 'border-blue-600' : 'border-transparent' }}"
                                    data-image-url="{{ $url }}" data-index="{{ $i }}" aria-label="View image {{ $i + 1 }}">
                                <img src="{{ $url }}" alt="{{ $product->name }} thumbnail {{ $i + 1 }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="relative flex-1 min-w-0">
                <div id="product-zoom-viewer" class="relative bg-gray-50 rounded-xl overflow-hidden cursor-zoom-in select-none" style="aspect-ratio: 1 / 1;">
                    <img id="product-zoom-image" src="{{ $images[0] }}" alt="{{ $product->name }}"
                         class="w-full h-full object-contain p-6 transition-transform duration-150 ease-out" style="transform-origin: center center;">
                    <span class="absolute top-3 left-3 text-xs font-medium px-2.5 py-1 rounded-md {{ $inStock ? 'bg-green-600 text-white' : 'bg-red-600 text-white' }}">
                        {{ $inStock ? 'In Stock' : 'Out of Stock' }}
                    </span>
                    <div id="product-zoom-hint" class="absolute bottom-2 right-2 text-[10px] bg-black/50 text-white px-2 py-1 rounded-full pointer-events-none opacity-80">Hover to zoom</div>
                </div>
                @if(count($images) > 1)
                    <button type="button" id="product-slider-prev" aria-label="Previous image" class="absolute left-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/90 shadow border border-gray-200 text-gray-600 hover:bg-white flex items-center justify-center">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                    <button type="button" id="product-slider-next" aria-label="Next image" class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/90 shadow border border-gray-200 text-gray-600 hover:bg-white flex items-center justify-center">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                @endif
            </div>
        </div>

        {{-- Buy box --}}
        <div>
            @if($product->category)
                <p class="text-sm text-blue-600 font-medium mb-1">{{ $product->category->name }}</p>
            @endif
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mb-3">{{ $product->name }}</h1>

            <a href="#reviews" class="inline-flex items-center gap-2 mb-4 group">
                <x-star-rating :rating="$reviewSummary['average']" />
                @if($reviewSummary['count'])
                    <span class="text-sm text-gray-600 group-hover:text-blue-600">{{ number_format($reviewSummary['average'], 1) }} ({{ $reviewSummary['count'] }} {{ Str::plural('review', $reviewSummary['count']) }})</span>
                @else
                    <span class="text-sm text-gray-500 group-hover:text-blue-600">No reviews yet</span>
                @endif
            </a>

            <div class="flex items-center gap-3 mb-4">
                <span class="text-3xl text-blue-600 font-bold">${{ number_format($product->finalPrice(), 2) }}</span>
                @if($product->discount_price)
                    <span class="text-gray-400 line-through">${{ number_format($product->price, 2) }}</span>
                    <span class="border border-red-200 bg-red-50 text-red-600 text-xs font-medium px-2 py-0.5 rounded-md">{{ $product->discountPercent() }}% OFF</span>
                @endif
            </div>

            <p class="text-gray-600 mb-5 leading-relaxed">{{ \Illuminate\Support\Str::limit($product->description ?: 'No description available.', 220) }}</p>

            <p class="text-sm mb-1 flex items-center gap-1.5 {{ $inStock ? 'text-green-600' : 'text-red-600' }}" data-stock-for="{{ $product->id }}-label">
                <i data-lucide="{{ $inStock ? 'check-circle-2' : 'x-circle' }}" class="w-4 h-4"></i>
                <span data-stock-for="{{ $product->id }}">{{ $product->stock }}</span>
                <span data-in-stock-for="{{ $product->id }}">{{ $inStock ? 'in stock' : 'Out of stock' }}</span>
                <span class="text-gray-400 ml-2">SKU: {{ $product->sku }}</span>
            </p>

            <div class="mt-5">
                <label for="qty-input" class="text-sm font-medium text-gray-700 block mb-2">Quantity</label>
                <div class="flex items-center gap-3 mb-5">
                    <div class="inline-flex items-center border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" data-qty-step="-1" aria-label="Decrease quantity" class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-50"><i data-lucide="minus" class="w-4 h-4"></i></button>
                        <input id="qty-input" type="number" value="1" min="1" max="{{ $maxQty }}" class="w-14 h-10 text-center border-0 border-x border-gray-200 text-sm focus:ring-0 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                        <button type="button" data-qty-step="1" aria-label="Increase quantity" class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-50"><i data-lucide="plus" class="w-4 h-4"></i></button>
                    </div>
                    <span class="text-xs text-gray-400">(Max {{ $maxQty }})</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" {{ $inStock ? '' : 'disabled' }}
                            onclick="window.addToCart({{ $product->id }}, parseInt(document.getElementById('qty-input').value, 10) || 1, this)"
                            class="border border-blue-600 text-blue-600 hover:bg-blue-50 disabled:border-gray-200 disabled:text-gray-400 disabled:hover:bg-transparent px-5 py-3 rounded-lg font-medium flex items-center justify-center gap-2 transition disabled:cursor-not-allowed">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i> Add to Cart
                    </button>
                    <form action="{{ route('cart.buy-now', $product) }}" method="POST">
                        @csrf
                        <input type="hidden" name="qty" id="buy-now-qty" value="1">
                        <button {{ $inStock ? '' : 'disabled' }} class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-200 disabled:text-gray-400 text-white px-5 py-3 rounded-lg font-medium flex items-center justify-center gap-2">
                            <i data-lucide="zap" class="w-4 h-4"></i> Buy Now
                        </button>
                    </form>
                </div>
            </div>

            @php $inWishlist = $wishlistIds->contains($product->id); @endphp
            <button type="button" data-wishlist-btn data-product-id="{{ $product->id }}" aria-pressed="{{ $inWishlist ? 'true' : 'false' }}"
                    onclick="window.toggleWishlist({{ $product->id }}, this)"
                    class="mt-3 w-full border px-5 py-2.5 rounded-lg text-sm flex items-center justify-center gap-2 transition {{ $inWishlist ? 'border-red-200 text-red-500' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                <i data-lucide="heart" class="w-4 h-4" fill="{{ $inWishlist ? 'currentColor' : 'none' }}"></i>
                <span data-wishlist-label>{{ $inWishlist ? 'Saved to Wishlist' : 'Add to Wishlist' }}</span>
            </button>
        </div>
    </div>

    {{-- Trust strip --}}
    @php
        $fs = \App\Models\Setting::freeShippingThreshold();
        $fsLabel = rtrim(rtrim(number_format($fs, 2), '0'), '.');
        $trust = [
            ['truck', 'Free Shipping', 'On orders over $' . $fsLabel],
            ['shield-check', 'Secure Payment', '100% secure'],
            ['refresh-cw', 'Easy Returns', '7 days return'],
            ['headphones', '24/7 Support', "We're here to help"],
        ];
    @endphp
    <div class="mt-6 bg-blue-50/60 border border-blue-100 rounded-2xl grid grid-cols-2 lg:grid-cols-4 gap-4 p-5">
        @foreach($trust as $t)
            @php [$icon, $title, $sub] = $t; @endphp
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-full bg-white shadow-sm flex items-center justify-center text-blue-600 shrink-0"><i data-lucide="{{ $icon }}" class="w-5 h-5"></i></div>
                <div><div class="text-sm font-semibold text-slate-900">{{ $title }}</div><div class="text-xs text-gray-500">{{ $sub }}</div></div>
            </div>
        @endforeach
    </div>

    {{-- Tabs --}}
    <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="flex gap-6 px-6 border-b border-gray-100 overflow-x-auto" role="tablist">
            <button type="button" role="tab" data-tab="description" aria-selected="true" class="product-tab py-4 text-sm font-medium border-b-2 border-blue-600 text-blue-600 whitespace-nowrap">Description</button>
            <button type="button" role="tab" data-tab="specs" aria-selected="false" class="product-tab py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-800 whitespace-nowrap">Specifications</button>
            <a href="#reviews" class="py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-800 whitespace-nowrap">Reviews ({{ $reviewSummary['count'] }})</a>
            <button type="button" role="tab" data-tab="faq" aria-selected="false" class="product-tab py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-800 whitespace-nowrap">FAQ</button>
        </div>

        <div class="p-6">
            <div data-panel="description" role="tabpanel" class="grid md:grid-cols-[1fr_320px] gap-8">
                <div>
                    <h2 class="font-bold text-lg text-slate-900 mb-3">Product Description</h2>
                    <p class="text-gray-600 leading-relaxed whitespace-pre-line">{{ $product->description ?: 'No description available.' }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-5 h-fit">
                    <h3 class="font-semibold text-slate-900 mb-3">Why choose us?</h3>
                    <ul class="space-y-3 text-sm">
                        <li class="flex gap-3"><i data-lucide="shield-check" class="w-5 h-5 text-blue-600 shrink-0"></i><span><span class="font-medium text-slate-900 block">Quality products</span><span class="text-gray-500 text-xs">Carefully selected for you</span></span></li>
                        <li class="flex gap-3"><i data-lucide="lock" class="w-5 h-5 text-blue-600 shrink-0"></i><span><span class="font-medium text-slate-900 block">Safe &amp; secure payment</span><span class="text-gray-500 text-xs">Cash on delivery or card</span></span></li>
                        <li class="flex gap-3"><i data-lucide="truck" class="w-5 h-5 text-blue-600 shrink-0"></i><span><span class="font-medium text-slate-900 block">Fast &amp; reliable delivery</span><span class="text-gray-500 text-xs">Free above ${{ $fsLabel }}</span></span></li>
                    </ul>
                </div>
            </div>

            <div data-panel="specs" role="tabpanel" class="hidden">
                <h2 class="font-bold text-lg text-slate-900 mb-3">Specifications</h2>
                <dl class="max-w-xl divide-y divide-gray-100 text-sm">
                    <div class="flex justify-between py-2.5"><dt class="text-gray-500">Category</dt><dd class="font-medium">{{ $product->category->name ?? '—' }}</dd></div>
                    <div class="flex justify-between py-2.5"><dt class="text-gray-500">SKU</dt><dd class="font-medium">{{ $product->sku }}</dd></div>
                    <div class="flex justify-between py-2.5"><dt class="text-gray-500">Availability</dt><dd class="font-medium">{{ $inStock ? 'In stock' : 'Out of stock' }}</dd></div>
                    <div class="flex justify-between py-2.5"><dt class="text-gray-500">Price</dt><dd class="font-medium">${{ number_format($product->finalPrice(), 2) }}</dd></div>
                </dl>
            </div>

            <div data-panel="faq" role="tabpanel" class="hidden max-w-2xl space-y-3">
                <h2 class="font-bold text-lg text-slate-900 mb-1">Frequently asked questions</h2>
                @foreach($faqs as [$q, $a])
                    <details class="group border border-gray-200 rounded-lg px-4 py-3">
                        <summary class="cursor-pointer text-sm font-medium text-slate-900 flex items-center justify-between list-none">
                            {{ $q }} <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform"></i>
                        </summary>
                        <p class="text-sm text-gray-600 mt-2">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </div>

    @include('shop.partials.reviews')

    @if($related->count())
        <div class="mt-12">
            <h2 class="text-xl font-bold mb-5 text-slate-900">Related Products</h2>
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
        // ---- Gallery -------------------------------------------------------
        var images = @json($images);
        var currentIndex = 0;
        var viewer = document.getElementById('product-zoom-viewer');
        var image = document.getElementById('product-zoom-image');
        var thumbButtons = document.querySelectorAll('.product-thumb');
        var prevBtn = document.getElementById('product-slider-prev');
        var nextBtn = document.getElementById('product-slider-next');

        if (viewer && image) {
            var setActiveThumb = function (index) {
                thumbButtons.forEach(function (btn) {
                    var isActive = parseInt(btn.dataset.index, 10) === index;
                    btn.classList.toggle('border-blue-600', isActive);
                    btn.classList.toggle('border-transparent', !isActive);
                });
            };
            var showImage = function (index) {
                if (!images.length) return;
                currentIndex = ((index % images.length) + images.length) % images.length;
                image.src = images[currentIndex];
                setActiveThumb(currentIndex);
            };
            thumbButtons.forEach(function (btn) {
                btn.addEventListener('click', function () { showImage(parseInt(btn.dataset.index, 10)); });
            });
            if (prevBtn) prevBtn.addEventListener('click', function () { showImage(currentIndex - 1); });
            if (nextBtn) nextBtn.addEventListener('click', function () { showImage(currentIndex + 1); });

            // Hover-to-zoom (skipped on touch devices).
            if (!window.matchMedia('(hover: none)').matches) {
                viewer.addEventListener('mousemove', function (e) {
                    var rect = viewer.getBoundingClientRect();
                    image.style.transformOrigin = ((e.clientX - rect.left) / rect.width * 100) + '% ' + ((e.clientY - rect.top) / rect.height * 100) + '%';
                    image.style.transform = 'scale(2)';
                });
                viewer.addEventListener('mouseleave', function () {
                    image.style.transform = 'scale(1)';
                    image.style.transformOrigin = 'center center';
                });
            }
        }

        // ---- Quantity stepper ---------------------------------------------
        var qty = document.getElementById('qty-input');
        var buyNowQty = document.getElementById('buy-now-qty');
        var syncBuyNowQty = function () { if (buyNowQty) buyNowQty.value = qty.value; };
        if (qty) { qty.addEventListener('input', syncBuyNowQty); qty.addEventListener('change', syncBuyNowQty); }
        document.querySelectorAll('[data-qty-step]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var min = parseInt(qty.min, 10) || 1, max = parseInt(qty.max, 10) || 1;
                var next = (parseInt(qty.value, 10) || 1) + parseInt(btn.dataset.qtyStep, 10);
                qty.value = Math.min(max, Math.max(min, next));
                syncBuyNowQty();
            });
        });

        // ---- Tabs ----------------------------------------------------------
        var tabs = document.querySelectorAll('.product-tab');
        var panels = document.querySelectorAll('[data-panel]');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) {
                    var on = t === tab;
                    t.setAttribute('aria-selected', on ? 'true' : 'false');
                    t.classList.toggle('border-blue-600', on);
                    t.classList.toggle('text-blue-600', on);
                    t.classList.toggle('border-transparent', !on);
                    t.classList.toggle('text-gray-500', !on);
                });
                panels.forEach(function (p) { p.classList.toggle('hidden', p.dataset.panel !== tab.dataset.tab); });
            });
        });

        // ---- Wishlist (front-end only, same behaviour as product cards) ----
    })();

    // Track this product view for the "Recently Viewed" strip.
    window.__currentProductId = {{ $product->id }};
    (function () {
        var snapshot = {
            id: {{ $product->id }},
            name: @json($product->name),
            url: @json(route('shop.show', $product->slug)),
            image: @json($product->imageUrlSmall()),
            price: '{{ number_format($product->finalPrice(), 2) }}',
        };
        try {
            var list = JSON.parse(localStorage.getItem('recently_viewed') || '[]');
            list = list.filter(function (p) { return p.id !== snapshot.id; });
            list.unshift(snapshot);
            localStorage.setItem('recently_viewed', JSON.stringify(list.slice(0, 10)));
        } catch (e) { /* localStorage unavailable — safe to ignore */ }
    })();
</script>

<x-recently-viewed />
@endsection
