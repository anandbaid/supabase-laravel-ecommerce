<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', "Let's Shop - Better Products. Happier You.")</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@0.474.0/dist/umd/lucide.js"></script>
    <style>
        body{font-family:'Poppins',sans-serif}
        @keyframes wishlistPop{0%{transform:scale(1)}35%{transform:scale(1.35)}60%{transform:scale(0.9)}100%{transform:scale(1)}}
        .wishlist-pop{animation:wishlistPop 0.4s ease}
        @keyframes cartBump{0%{transform:scale(1)}40%{transform:scale(1.4)}100%{transform:scale(1)}}
        .cart-bump{animation:cartBump 0.35s ease}
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <img src="{{ asset('images/logo.png') }}" alt="Let's Shop" class="h-10 w-auto">
        </a>

        <nav class="hidden md:flex items-center gap-6 font-medium text-sm">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-blue-600' : 'text-gray-700' }} hover:text-blue-600">Home</a>
            <a href="{{ route('shop.index') }}" class="{{ request()->routeIs('shop.*') && !request('deals') ? 'text-blue-600' : 'text-gray-700' }} hover:text-blue-600">Shop</a>

            <div class="relative" data-categories-menu>
                <button type="button" data-categories-trigger aria-haspopup="true" aria-expanded="false" class="flex items-center gap-1 text-gray-700 hover:text-blue-600">
                    Categories <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                </button>
                <div data-categories-panel class="hidden absolute left-0 top-full pt-3 z-20">
                    <div class="bg-white border rounded-xl shadow-lg p-4 grid grid-cols-2 gap-x-8 gap-y-4 w-[420px]">
                        @php $navCategories = \App\Models\Category::where('is_active', true)->topLevel()->with(['children' => fn ($q) => $q->where('is_active', true)])->get(); @endphp
                        @forelse($navCategories as $navCat)
                            @continue(!($navCat instanceof \App\Models\Category))
                            <div>
                                <a href="{{ route('shop.index', ['category' => $navCat->slug]) }}" class="font-semibold text-gray-800 hover:text-blue-600 text-sm">{{ $navCat->name }}</a>
                                @if($navCat->children->isNotEmpty())
                                    <ul class="mt-1.5 space-y-1">
                                        @foreach($navCat->children as $child)
                                            <li><a href="{{ route('shop.index', ['category' => $child->slug]) }}" class="text-gray-500 hover:text-blue-600 text-xs">{{ $child->name }}</a></li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 col-span-2">No categories yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <a href="{{ route('shop.index', ['deals' => 1]) }}" class="{{ request('deals') ? 'text-blue-600' : 'text-gray-700' }} hover:text-blue-600">Deals</a>
            <a href="{{ route('static.about') }}" class="{{ request()->routeIs('static.about') ? 'text-blue-600' : 'text-gray-700' }} hover:text-blue-600">About</a>
            <a href="{{ route('static.contact') }}" class="{{ request()->routeIs('static.contact') ? 'text-blue-600' : 'text-gray-700' }} hover:text-blue-600">Contact</a>
        </nav>

        <form action="{{ route('shop.index') }}" method="GET" class="hidden md:flex flex-1 max-w-sm relative" id="header-search-form" autocomplete="off">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search for products..." class="w-full border border-gray-200 rounded-l-full px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" id="header-search-input">
            <button class="bg-blue-600 text-white rounded-r-full px-4 flex items-center justify-center"><i data-lucide="search" class="w-4 h-4"></i></button>
            <div id="header-search-suggestions" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border rounded-xl shadow-lg overflow-hidden z-50"></div>
        </form>

        <div class="flex items-center gap-4">
            @auth
                <div class="relative" data-account-menu>
                    <button type="button" data-account-menu-trigger aria-haspopup="true" aria-expanded="false" class="flex items-center gap-1 text-sm font-medium text-gray-700 hover:text-blue-600">
                        {{ auth()->user()->name }} <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </button>
                    <div data-account-menu-panel class="hidden absolute right-0 top-full pt-2 w-44 z-20">
                        <div class="bg-white border rounded-lg shadow-lg py-1 text-sm">
                            @unless(auth()->user()->isAdmin())
                                <a href="{{ route('account.dashboard') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50">
                                    <i data-lucide="user-circle" class="w-4 h-4"></i> My Account
                                </a>
                                <a href="{{ route('account.orders.index') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50">
                                    <i data-lucide="package" class="w-4 h-4"></i> My Orders
                                </a>
                                <a href="{{ route('wishlist.index') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50">
                                    <i data-lucide="heart" class="w-4 h-4"></i> Wishlist
                                    @if($wishlistIds->count())
                                        <span class="ml-auto text-xs text-gray-400">{{ $wishlistIds->count() }}</span>
                                    @endif
                                </a>
                                <a href="{{ route('addresses.index') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50">
                                    <i data-lucide="map-pin" class="w-4 h-4"></i> My Addresses
                                </a>
                                <a href="{{ route('account.edit') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50">
                                    <i data-lucide="shield" class="w-4 h-4"></i> Privacy Settings
                                </a>
                            @else
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Admin Panel
                                </a>
                            @endunless
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="w-full text-left px-4 py-2 hover:bg-gray-50">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600">
                    <i data-lucide="user" class="w-5 h-5"></i>
                </a>
            @endauth
            @auth
                @unless(auth()->user()->isAdmin())
                    <a href="{{ route('wishlist.index') }}" class="relative text-gray-700 hover:text-red-500" aria-label="Wishlist">
                        <i data-lucide="heart" class="w-6 h-6"></i>
                        <span data-wishlist-count class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center {{ $wishlistIds->count() ? '' : 'hidden' }}">{{ $wishlistIds->count() }}</span>
                    </a>
                @endunless
            @endauth
            <a href="{{ route('cart.index') }}" class="relative text-gray-700 hover:text-blue-600" aria-label="Cart">
                <i data-lucide="shopping-cart" class="w-6 h-6"></i>
                @php $cartCount = array_sum(session('cart', [])); @endphp
                <span data-cart-count class="absolute -top-2 -right-2 bg-blue-600 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
            </a>
        </div>
    </div>
</header>

@include('partials.toasts')

<main>
    @yield('content')
</main>

<footer class="bg-slate-900 text-gray-300 mt-16">
    <div class="max-w-7xl mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-5 gap-8">
        <div class="md:col-span-1">
            <div class="bg-white inline-block rounded-lg px-3 py-2 mb-3">
                <img src="{{ asset('images/logo.png') }}" alt="Let's Shop" class="h-7 w-auto">
            </div>
            <p class="text-sm text-gray-400">Better Products. Happier You.</p>
            <div class="flex items-center gap-3 mt-4">
                <a href="#" aria-label="Facebook" class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center hover:bg-blue-600 transition"><i data-lucide="facebook" class="w-4 h-4"></i></a>
                <a href="#" aria-label="Twitter" class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center hover:bg-blue-600 transition"><i data-lucide="twitter" class="w-4 h-4"></i></a>
                <a href="#" aria-label="Instagram" class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center hover:bg-blue-600 transition"><i data-lucide="instagram" class="w-4 h-4"></i></a>
                <a href="#" aria-label="LinkedIn" class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center hover:bg-blue-600 transition"><i data-lucide="linkedin" class="w-4 h-4"></i></a>
            </div>
        </div>

        <div>
            <h4 class="text-white font-semibold mb-3">Quick Links</h4>
            <ul class="text-sm space-y-2 text-gray-400">
                <li><a href="{{ route('home') }}" class="hover:text-white">Home</a></li>
                <li><a href="{{ route('shop.index') }}" class="hover:text-white">Shop</a></li>
                <li><a href="{{ route('shop.index', ['deals' => 1]) }}" class="hover:text-white">Deals</a></li>
                <li><a href="{{ route('static.about') }}" class="hover:text-white">About Us</a></li>
                <li><a href="{{ route('static.contact') }}" class="hover:text-white">Contact</a></li>
            </ul>
        </div>

        <div>
            <h4 class="text-white font-semibold mb-3">Information</h4>
            <ul class="text-sm space-y-2 text-gray-400">
                <li><a href="{{ route('static.privacy') }}" class="hover:text-white">Privacy Policy</a></li>
                <li><a href="{{ route('static.privacy') }}" class="hover:text-white">Terms &amp; Conditions</a></li>
                <li><a href="{{ route('static.returns') }}" class="hover:text-white">Return Policy</a></li>
                <li><a href="{{ route('static.returns') }}" class="hover:text-white">Shipping Policy</a></li>
            </ul>
        </div>

        <div>
            <h4 class="text-white font-semibold mb-3">Customer Service</h4>
            <ul class="text-sm space-y-2 text-gray-400">
                <li><a href="{{ route('static.contact') }}" class="hover:text-white">Help Center</a></li>
                @auth
                    @unless(auth()->user()->isAdmin())
                        <li><a href="{{ route('account.orders.index') }}" class="hover:text-white">Track Order</a></li>
                        <li><a href="{{ route('account.dashboard') }}" class="hover:text-white">My Account</a></li>
                    @endunless
                @else
                    <li><a href="{{ route('login') }}" class="hover:text-white">Track Order</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white">My Account</a></li>
                @endauth
                <li><a href="{{ route('static.returns') }}" class="hover:text-white">Returns &amp; Refunds</a></li>
                <li><a href="{{ route('static.contact') }}" class="hover:text-white">Support</a></li>
            </ul>
        </div>

        <div>
            <h4 class="text-white font-semibold mb-3">Newsletter</h4>
            <p class="text-sm text-gray-400 mb-2">Subscribe to get updates and offers.</p>
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="flex">
                @csrf
                <input type="email" name="email" required placeholder="Your email address" class="rounded-l-lg px-3 py-2 text-sm text-gray-800 w-full">
                <button type="submit" class="bg-blue-600 px-4 rounded-r-lg text-white text-sm whitespace-nowrap">Subscribe</button>
            </form>
        </div>
    </div>

    <div class="border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-gray-500">&copy; {{ date('Y') }} Let's Shop. All rights reserved.</p>
            <div class="flex items-center gap-3 text-gray-500 text-xs font-semibold tracking-wide">
                <span class="px-2 py-1 border border-gray-700 rounded">VISA</span>
                <span class="px-2 py-1 border border-gray-700 rounded">Mastercard</span>
                <span class="px-2 py-1 border border-gray-700 rounded">PayPal</span>
                <span class="px-2 py-1 border border-gray-700 rounded">Stripe</span>
            </div>
        </div>
    </div>
</footer>

@include('partials.realtime')

<script>
    if (window.lucide) lucide.createIcons();

    // Header account menu: click to open/close (no hover, so it never "vanishes" on the way to it).
    (function () {
        var wrap = document.querySelector('[data-account-menu]');
        if (!wrap) return;
        var trigger = wrap.querySelector('[data-account-menu-trigger]');
        var panel = wrap.querySelector('[data-account-menu-panel]');

        function close() {
            panel.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
        }
        function toggle() {
            var open = panel.classList.contains('hidden');
            panel.classList.toggle('hidden', !open);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        trigger.addEventListener('click', function (e) { e.stopPropagation(); toggle(); });
        document.addEventListener('click', function (e) { if (!wrap.contains(e.target)) close(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    })();

    // Header categories menu — same click-to-toggle behavior as the account menu.
    (function () {
        var wrap = document.querySelector('[data-categories-menu]');
        if (!wrap) return;
        var trigger = wrap.querySelector('[data-categories-trigger]');
        var panel = wrap.querySelector('[data-categories-panel]');

        function close() {
            panel.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
        }
        function toggle() {
            var open = panel.classList.contains('hidden');
            panel.classList.toggle('hidden', !open);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        trigger.addEventListener('click', function (e) { e.stopPropagation(); toggle(); });
        document.addEventListener('click', function (e) { if (!wrap.contains(e.target)) close(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    })();

    // Shared AJAX wishlist toggle. Any button anywhere on the page can call
    // window.toggleWishlist(productId, button) — used by product cards and
    // the product detail page. Keeps every heart for that product in sync
    // and updates the header count badge.
    window.toggleWishlist = function (productId, button) {
        if (button) button.disabled = true;
        var token = document.querySelector('meta[name="csrf-token"]').content;

        fetch('/wishlist/' + productId + '/toggle', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        })
            .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
            .then(function (result) {
                if (!result.ok) {
                    if (result.data.redirect) { window.location.href = result.data.redirect; return; }
                    window.showToast(result.data.message || 'Something went wrong.', 'error');
                    return;
                }

                document.querySelectorAll('[data-wishlist-btn][data-product-id="' + productId + '"]').forEach(function (btn) {
                    btn.classList.toggle('text-red-500', result.data.wishlisted);
                    btn.classList.toggle('text-gray-400', !result.data.wishlisted);
                    btn.setAttribute('aria-pressed', result.data.wishlisted ? 'true' : 'false');
                    var icon = btn.querySelector('i, svg');
                    if (icon) icon.setAttribute('fill', result.data.wishlisted ? 'currentColor' : 'none');
                    var label = btn.querySelector('[data-wishlist-label]');
                    if (label) label.textContent = result.data.wishlisted ? 'Saved to Wishlist' : 'Add to Wishlist';
                });

                var badge = document.querySelector('[data-wishlist-count]');
                if (badge) {
                    badge.textContent = result.data.count;
                    badge.classList.toggle('hidden', result.data.count < 1);
                }

                window.showToast(result.data.message, result.data.wishlisted ? 'success' : 'info', 3000);

                // A quick pop so the heart feels responsive, not just a color swap.
                document.querySelectorAll('[data-wishlist-btn][data-product-id="' + productId + '"] i, [data-wishlist-btn][data-product-id="' + productId + '"] svg').forEach(function (icon) {
                    icon.classList.remove('wishlist-pop');
                    void icon.offsetWidth; // restart the animation if clicked again quickly
                    icon.classList.add('wishlist-pop');
                });
            })
            .catch(function () { window.showToast('Could not update your wishlist. Please try again.', 'error'); })
            .finally(function () { if (button) button.disabled = false; });
    };

    // Shared AJAX add-to-cart. Call window.addToCart(productId, qty, button)
    // from any "Add to Cart" button — used by product cards and the product
    // page. Walks the button through idle -> adding -> added states so the
    // customer sees the action register, then updates the header cart badge.
    window.addToCart = function (productId, qty, button) {
        if (!button || button.dataset.state === 'busy') return;

        var original = button.innerHTML;
        button.dataset.state = 'busy';
        button.disabled = true;
        button.classList.add('opacity-90');
        button.innerHTML = '<svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Adding…';

        var token = document.querySelector('meta[name="csrf-token"]').content;

        fetch('/cart/' + productId + '/add', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ qty: qty || 1 }),
        })
            .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
            .then(function (result) {
                if (!result.ok) {
                    window.showToast(result.data.message || 'Could not add that to your cart.', 'error');
                    button.innerHTML = original;
                    button.dataset.state = 'idle';
                    button.disabled = false;
                    button.classList.remove('opacity-90');
                    return;
                }

                var badge = document.querySelector('[data-cart-count]');
                if (badge) {
                    badge.textContent = result.data.cartCount;
                    badge.classList.remove('hidden');
                    badge.classList.remove('cart-bump');
                    void badge.offsetWidth;
                    badge.classList.add('cart-bump');
                }

                button.classList.remove('opacity-90');
                button.classList.add('bg-green-600', 'hover:bg-green-600', 'border-green-600', 'text-white');
                button.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> Added';
                if (window.lucide) lucide.createIcons();

                window.showToast(result.data.message, result.data.capped ? 'info' : 'success', 2500);

                setTimeout(function () {
                    button.innerHTML = original;
                    button.dataset.state = 'idle';
                    button.disabled = false;
                    button.classList.remove('bg-green-600', 'hover:bg-green-600', 'border-green-600', 'text-white');
                    if (window.lucide) lucide.createIcons();
                }, 1400);
            })
            .catch(function () {
                window.showToast('Could not add that to your cart. Please try again.', 'error');
                button.innerHTML = original;
                button.dataset.state = 'idle';
                button.disabled = false;
                button.classList.remove('opacity-90');
            });
    };
</script>

<script>
(function () {
    var input = document.getElementById('header-search-input');
    var box = document.getElementById('header-search-suggestions');
    var form = document.getElementById('header-search-form');
    if (!input || !box) return;

    var debounceTimer = null;

    function hide() { box.classList.add('hidden'); box.innerHTML = ''; }

    function render(items) {
        if (!items.length) { hide(); return; }
        box.innerHTML = items.map(function (p) {
            return '<a href="' + p.url + '" class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50">' +
                '<img src="' + p.image + '" class="w-10 h-10 object-contain rounded bg-gray-50" alt="">' +
                '<span class="flex-1 text-sm text-gray-800 truncate">' + p.name + '</span>' +
                '<span class="text-sm font-semibold text-blue-600">$' + p.price + '</span>' +
                '</a>';
        }).join('');
        box.classList.remove('hidden');
    }

    input.addEventListener('input', function () {
        var q = input.value.trim();
        clearTimeout(debounceTimer);
        if (q.length < 2) { hide(); return; }
        debounceTimer = setTimeout(function () {
            fetch('{{ route("shop.suggest") }}?q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(render)
                .catch(hide);
        }, 250);
    });

    document.addEventListener('click', function (e) {
        if (form && !form.contains(e.target)) hide();
    });
})();
</script>
@stack('scripts')

</body>
</html>
