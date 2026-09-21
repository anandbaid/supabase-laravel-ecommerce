@extends('layouts.admin')
@section('page-title', 'Categories')

@section('content')
<div class="flex items-center justify-end mb-5">
    <a href="{{ route('admin.categories.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium">+ Add Category</a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-left">
            <tr>
                <th class="px-4 py-3">Category</th>
                <th class="px-4 py-3">Products</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($categories as $cat)
                <tr>
                    <td class="px-4 py-3 flex items-center gap-3">
                        <img src="{{ $cat->imageUrl() }}" class="w-10 h-10 object-contain bg-gray-50 rounded-lg">
                        <div>
                            <span class="font-medium">{{ $cat->name }}</span>
                            @if($cat->parent)
                                <div class="text-xs text-gray-400">Subcategory of {{ $cat->parent->name }}</div>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3">{{ $cat->products_count }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs {{ $cat->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $cat->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.categories.edit', $cat) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No categories found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $categories->links() }}</div>
@endsection
