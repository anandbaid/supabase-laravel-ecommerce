@extends('layouts.admin')
@section('page-title', 'Customers')

@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <form action="{{ route('admin.customers.index') }}" method="GET" class="relative">
        <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customers..." class="border rounded-lg pl-9 pr-3 py-2 text-sm w-64 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
    </form>
    <a href="{{ route('admin.customers.create') }}" class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
        <i data-lucide="user-plus" class="w-4 h-4"></i> Add Account
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-left">
            <tr>
                <th class="px-4 py-3"><span class="inline-flex items-center gap-1.5"><i data-lucide="user" class="w-3.5 h-3.5"></i> Name</span></th>
                <th class="px-4 py-3"><span class="inline-flex items-center gap-1.5"><i data-lucide="mail" class="w-3.5 h-3.5"></i> Email</span></th>
                <th class="px-4 py-3"><span class="inline-flex items-center gap-1.5"><i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i> Orders</span></th>
                <th class="px-4 py-3"><span class="inline-flex items-center gap-1.5"><i data-lucide="circle-dollar-sign" class="w-3.5 h-3.5"></i> Total Spent</span></th>
                <th class="px-4 py-3"><span class="inline-flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5"></i> Joined</span></th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($customers as $c)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ $c->name }}</td>
                    <td class="px-4 py-3">{{ $c->email }}</td>
                    <td class="px-4 py-3">{{ $c->orders_count }}</td>
                    <td class="px-4 py-3">${{ number_format($c->total_spent ?? 0, 2) }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $c->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center gap-3">
                            <a href="{{ route('admin.customers.show', $c) }}" class="inline-flex items-center gap-1 text-gray-500 hover:underline" title="View">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
                            </a>
                            <a href="{{ route('admin.customers.edit', $c) }}" class="inline-flex items-center gap-1 text-blue-600 hover:underline" title="Edit">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                            </a>
                            <form action="{{ route('admin.customers.destroy', $c) }}" method="POST" onsubmit="return confirm('Delete this account? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button class="inline-flex items-center gap-1 text-red-500 hover:underline" title="Delete">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                        <i data-lucide="users" class="w-8 h-8 mx-auto mb-2"></i>
                        No customers found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $customers->links() }}</div>
@endsection
