@php
    $navItem = fn ($route, $icon, $label, $pattern = null) => [
        'active' => request()->routeIs($pattern ?? $route),
        'href' => route($route),
        'icon' => $icon,
        'label' => $label,
    ];
    $items = [
        $navItem('account.dashboard', 'layout-dashboard', 'Dashboard'),
        $navItem('account.orders.index', 'package', 'Orders', 'account.orders.*'),
        $navItem('wishlist.index', 'heart', 'Wishlist'),
        $navItem('addresses.index', 'map-pin', 'Addresses', 'addresses.*'),
        $navItem('account.edit', 'user', 'Account Details'),
        $navItem('account.edit', 'shield', 'Privacy Settings'),
    ];
@endphp
<div class="bg-white rounded-xl shadow-sm p-3 h-fit">
    <nav class="space-y-1 text-sm">
        @foreach($items as $item)
            <a href="{{ $item['href'] }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg {{ $item['active'] ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4"></i> {{ $item['label'] }}
            </a>
        @endforeach
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 text-left">
                <i data-lucide="log-out" class="w-4 h-4"></i> Logout
            </button>
        </form>
    </nav>
</div>
