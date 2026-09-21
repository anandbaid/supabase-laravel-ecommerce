@extends('layouts.admin')
@section('page-title', 'Coupons')

@section('content')
<div class="flex items-center justify-end mb-5">
    <a href="{{ route('admin.coupons.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium">+ Add Coupon</a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-left">
            <tr>
                <th class="px-4 py-3">Code</th>
                <th class="px-4 py-3">Discount</th>
                <th class="px-4 py-3">Min Order</th>
                <th class="px-4 py-3">Usage</th>
                <th class="px-4 py-3">Expires</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($coupons as $coupon)
                <tr>
                    <td class="px-4 py-3 font-mono font-semibold">{{ $coupon->code }}</td>
                    <td class="px-4 py-3">
                        @if($coupon->type === 'percent')
                            {{ rtrim(rtrim(number_format($coupon->value, 2), '0'), '.') }}%
                            @if($coupon->max_discount_amount)
                                <span class="text-xs text-gray-400">(max ${{ number_format($coupon->max_discount_amount, 2) }})</span>
                            @endif
                        @else
                            ${{ number_format($coupon->value, 2) }} off
                        @endif
                    </td>
                    <td class="px-4 py-3">${{ number_format($coupon->min_order_amount, 2) }}</td>
                    <td class="px-4 py-3">{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / ' . $coupon->usage_limit : '' }}</td>
                    <td class="px-4 py-3">{{ $coupon->expires_at ? $coupon->expires_at->format('M d, Y') : '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs {{ $coupon->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline" onsubmit="return confirm('Delete this coupon?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No coupons yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $coupons->links() }}</div>
@endsection
