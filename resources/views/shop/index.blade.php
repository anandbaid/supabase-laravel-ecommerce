@extends('layouts.app')
@section('title', 'Shop - ShopEase')

@section('content')
<section class="relative bg-blue-50 overflow-hidden">
    <img src="{{ asset('images/shop-banner.png') }}" alt="" class="absolute inset-0 w-full h-full object-cover object-right pointer-events-none select-none">
    <div class="relative max-w-7xl mx-auto px-4 py-14 md:py-16">
        <div class="max-w-xl">
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-3">Our <span class="text-blue-600">Products</span></h1>
            <p class="text-gray-600">Discover amazing products at the best prices. Shop your favorites and enjoy a better experience.</p>
        </div>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-4 gap-8">
    <aside class="md:col-span-1">
        <form action="{{ route('shop.index') }}" method="GET" class="bg-white p-5 rounded-xl shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="font-semibold text-sm flex items-center gap-2">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4 text-blue-600"></i> Filter Products
                </h3>
                <a href="{{ route('shop.index') }}" class="text-xs text-blue-600 hover:underline">Clear All</a>
            </div>

            <div>
                <h3 class="font-semibold mb-2 text-sm flex items-center gap-1.5">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400"></i> Search
                </h3>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..." class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <div>
                <h3 class="font-semibold mb-2 text-sm flex items-center gap-1.5">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-gray-400"></i> Category
                </h3>
                <select name="category" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        @if($cat->children->isNotEmpty())
                            <optgroup label="{{ $cat->name }}">
                                <option value="{{ $cat->slug }}" @selected(request('category') == $cat->slug)>All {{ $cat->name }}</option>
                                @foreach($cat->children as $child)
                                    <option value="{{ $child->slug }}" @selected(request('category') == $child->slug)>— {{ $child->name }}</option>
                                @endforeach
                            </optgroup>
                        @else
                            <option value="{{ $cat->slug }}" @selected(request('category') == $cat->slug)>{{ $cat->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div>
                <h3 class="font-semibold mb-2 text-sm flex items-center gap-1.5">
                    <i data-lucide="dollar-sign" class="w-4 h-4 text-gray-400"></i> Price Range
                </h3>
                <div class="flex items-center gap-2">
                    <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <span class="text-gray-400">—</span>
                    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
            </div>

            <div>
                <h3 class="font-semibold mb-2 text-sm flex items-center gap-1.5">
                    <i data-lucide="arrow-up-down" class="w-4 h-4 text-gray-400"></i> Sort By
                </h3>
                <select name="sort" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="latest" @selected(request('sort','latest')=='latest')>Latest</option>
                    <option value="price_low" @selected(request('sort')=='price_low')>Price: Low to High</option>
                    <option value="price_high" @selected(request('sort')=='price_high')>Price: High to Low</option>
                    <option value="name" @selected(request('sort')=='name')>Name</option>
                </select>
            </div>

            <button class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                <i data-lucide="sliders-horizontal" class="w-4 h-4"></i> Apply Filters
            </button>
        </form>
    </aside>

    <div class="md:col-span-3">
        <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <p class="text-sm text-gray-500 flex items-center gap-2">
                <i data-lucide="package" class="w-4 h-4 text-blue-600"></i> {{ $products->total() }} products found
            </p>
            <div class="flex items-center gap-3">
                <form action="{{ route('shop.index') }}" method="GET" class="flex items-center gap-2">
                    @foreach(request()->except('sort') as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <label class="text-sm text-gray-500 hidden sm:block">Sort by:</label>
                    <select name="sort" onchange="this.form.submit()" class="border rounded-full px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="latest" @selected(request('sort','latest')=='latest')>Latest</option>
                        <option value="price_low" @selected(request('sort')=='price_low')>Price: Low to High</option>
                        <option value="price_high" @selected(request('sort')=='price_high')>Price: High to Low</option>
                        <option value="name" @selected(request('sort')=='name')>Name</option>
                    </select>
                </form>
                <div class="flex items-center border rounded-lg overflow-hidden">
                    <button type="button" id="view-grid-btn" class="p-2 bg-blue-600 text-white" title="Grid view">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i>
                    </button>
                    <button type="button" id="view-list-btn" class="p-2 text-gray-500 hover:bg-gray-50" title="List view">
                        <i data-lucide="list" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="products-grid" class="grid grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($products as $product)
                @include('shop.partials.card', ['product' => $product])
            @empty
                <div class="col-span-full text-center py-16 text-gray-400">
                    <i data-lucide="package-search" class="w-10 h-10 mx-auto mb-3"></i>
                    No products found.
                </div>
            @endforelse
        </div>

        <div class="mt-8">{{ $products->links() }}</div>
    </div>
</div>

<script>
(function () {
    var grid = document.getElementById('products-grid');
    var gridBtn = document.getElementById('view-grid-btn');
    var listBtn = document.getElementById('view-list-btn');
    if (!grid || !gridBtn || !listBtn) return;

    function setActive(active, inactive) {
        active.classList.add('bg-blue-600', 'text-white');
        active.classList.remove('text-gray-500');
        inactive.classList.remove('bg-blue-600', 'text-white');
        inactive.classList.add('text-gray-500');
    }

    gridBtn.addEventListener('click', function () {
        grid.classList.remove('grid-cols-1');
        grid.classList.add('grid-cols-2', 'lg:grid-cols-3');
        setActive(gridBtn, listBtn);
    });

    listBtn.addEventListener('click', function () {
        grid.classList.remove('grid-cols-2', 'lg:grid-cols-3');
        grid.classList.add('grid-cols-1');
        setActive(listBtn, gridBtn);
    });
})();
</script>
@endsection