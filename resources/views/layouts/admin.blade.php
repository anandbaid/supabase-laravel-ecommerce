<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', "Admin - Let's Shop")</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@0.474.0/dist/umd/lucide.js"></script>
    <style>body{font-family:'Poppins',sans-serif}</style>
</head>
<body class="bg-gray-50 text-gray-800 flex">

<aside class="w-64 bg-slate-900 text-gray-300 min-h-screen sticky top-0">
    <div class="flex items-center gap-2 px-5 py-5 border-b border-slate-800">
        <div class="bg-white rounded-lg p-1.5">
            <img src="{{ asset('images/logo.png') }}" alt="Let's Shop" class="h-7 w-auto">
        </div>
    </div>
    <nav class="px-3 py-4 space-y-1 text-sm">
        @php $link = fn($active, $route) => 'flex items-center gap-3 px-3 py-2.5 rounded-lg ' . ($active ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 text-gray-300'); @endphp
        <a href="{{ route('admin.dashboard') }}" class="{{ $link(request()->routeIs('admin.dashboard'), 'admin.dashboard') }}">
            <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
        </a>
        <a href="{{ route('admin.products.index') }}" class="{{ $link(request()->routeIs('admin.products.*'), 'admin.products.index') }}">
            <i data-lucide="package" class="w-4 h-4"></i> Products
        </a>
        <a href="{{ route('admin.categories.index') }}" class="{{ $link(request()->routeIs('admin.categories.*'), 'admin.categories.index') }}">
            <i data-lucide="folder-tree" class="w-4 h-4"></i> Categories
        </a>
        <a href="{{ route('admin.coupons.index') }}" class="{{ $link(request()->routeIs('admin.coupons.*'), 'admin.coupons.index') }}">
            <i data-lucide="ticket-percent" class="w-4 h-4"></i> Coupons
        </a>
        <a href="{{ route('admin.settings.tax') }}" class="{{ $link(request()->routeIs('admin.settings.*'), 'admin.settings.tax') }}">
            <i data-lucide="percent" class="w-4 h-4"></i> Tax Settings
        </a>
        <a href="{{ route('admin.orders.index') }}" class="{{ $link(request()->routeIs('admin.orders.*'), 'admin.orders.index') }}">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i> Orders
        </a>
        <a href="{{ route('admin.customers.index') }}" class="{{ $link(request()->routeIs('admin.customers.*'), 'admin.customers.index') }}">
            <i data-lucide="users" class="w-4 h-4"></i> Customers
        </a>
        <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-gray-300 mt-4">
            <i data-lucide="external-link" class="w-4 h-4"></i> View Store
        </a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-gray-300">
                <i data-lucide="log-out" class="w-4 h-4"></i> Logout
            </button>
        </form>
    </nav>
</aside>

<div class="flex-1 min-h-screen">
    <header class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold">@yield('page-title', 'Dashboard')</h1>
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-semibold">
                {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
            </div>
            <div class="text-sm">
                <div class="font-medium">{{ auth()->user()->name ?? 'Admin' }}</div>
                <div class="text-xs text-gray-400">Super Admin</div>
            </div>
        </div>
    </header>

    <div class="p-6">
        @yield('content')
    </div>
</div>

@include('partials.toasts')
@include('partials.realtime')

<script>
    if (window.lucide) lucide.createIcons();
</script>

</body>
</html>