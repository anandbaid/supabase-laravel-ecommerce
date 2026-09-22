<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', "Let's Shop - Better Products. Happier You.")</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@0.474.0/dist/umd/lucide.js"></script>
    <style>body{font-family:'Poppins',sans-serif}</style>
</head>
<body class="bg-gray-50 text-gray-800">

<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <img src="{{ asset('images/logo.png') }}" alt="Let's Shop" class="h-10 w-auto">
        </a>

        <nav class="hidden md:flex items-center gap-6 font-medium text-sm">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-blue-600' : 'text-gray-700' }} hover:text-blue-600">Home</a>
            <a href="{{ route('shop.index') }}" class="{{ request()->routeIs('shop.*') ? 'text-blue-600' : 'text-gray-700' }} hover:text-blue-600">Shop</a>
        </nav>

        <form action="{{ route('shop.index') }}" method="GET" class="hidden md:flex flex-1 max-w-sm">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search for products..." class="w-full border border-gray-200 rounded-l-full px-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
            <button class="bg-blue-600 text-white rounded-r-full px-4 flex items-center justify-center"><i data-lucide="search" class="w-4 h-4"></i></button>
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
                                <a href="{{ route('account.edit') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50">
                                    <i data-lucide="user-circle" class="w-4 h-4"></i> My Account
                                </a>
                                <a href="{{ route('addresses.index') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50">
                                    <i data-lucide="map-pin" class="w-4 h-4"></i> My Addresses
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
            <a href="{{ route('cart.index') }}" class="relative text-gray-700 hover:text-blue-600">
                <i data-lucide="shopping-cart" class="w-6 h-6"></i>
                @php $cartCount = array_sum(session('cart', [])); @endphp
                @if($cartCount > 0)
                    <span class="absolute -top-2 -right-2 bg-blue-600 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center">{{ $cartCount }}</span>
                @endif
            </a>
        </div>
    </div>
</header>

@include('partials.toasts')

<main>
    @yield('content')
</main>

<footer class="bg-slate-900 text-gray-300 mt-16">
    <div class="max-w-7xl mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
            <div class="bg-white inline-block rounded-lg px-3 py-2 mb-2">
                <img src="{{ asset('images/logo.png') }}" alt="Let's Shop" class="h-7 w-auto">
            </div>
            <p class="text-sm text-gray-400">Better Products. Happier You.</p>
        </div>
        <div>
            <h4 class="text-white font-semibold mb-3">Quick Links</h4>
            <ul class="text-sm space-y-2 text-gray-400">
                <li><a href="{{ route('home') }}" class="hover:text-white">Home</a></li>
                <li><a href="{{ route('shop.index') }}" class="hover:text-white">Shop</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-white font-semibold mb-3">Customer Service</h4>
            <ul class="text-sm space-y-2 text-gray-400">
                <li>FAQs</li><li>Shipping Policy</li><li>Return Policy</li>
            </ul>
        </div>
        <div>
            <h4 class="text-white font-semibold mb-3">Newsletter</h4>
            <p class="text-sm text-gray-400 mb-2">Subscribe to get updates and offers.</p>
            <div class="flex">
                <input type="email" placeholder="Your email" class="rounded-l-lg px-3 py-2 text-sm text-gray-800 w-full">
                <button class="bg-blue-600 px-4 rounded-r-lg text-white text-sm">Subscribe</button>
            </div>
        </div>
    </div>
    <div class="text-center text-xs text-gray-500 border-t border-gray-800 py-4">&copy; {{ date('Y') }} Let's Shop. All rights reserved.</div>
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
</script>
@stack('scripts')

</body>
</html>