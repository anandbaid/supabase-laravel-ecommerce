@extends('layouts.app')
@section('title', 'My Addresses - ShopEase')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">My Addresses</h1>
        <a href="{{ route('addresses.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium">+ Add New Address</a>
    </div>

    @if(empty($addresses) || $addresses->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <p class="text-gray-400 mb-4">You haven't saved any addresses yet.</p>
            <a href="{{ route('addresses.create') }}" class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm">Add an Address</a>
        </div>
    @else
        <div class="grid md:grid-cols-2 gap-4">
            @foreach($addresses as $address)
                <div class="bg-white rounded-xl shadow-sm p-5 relative">
                    @if($address->is_default)
                        <span class="absolute top-4 right-4 text-xs font-medium px-2 py-1 rounded-full bg-blue-100 text-blue-700">Default</span>
                    @endif
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1">{{ $address->label }}</div>
                    <div class="font-semibold">{{ $address->full_name }}</div>
                    <p class="text-sm text-gray-600 mt-1 whitespace-pre-line">{{ $address->line1 }}{{ $address->line2 ? ', ' . $address->line2 : '' }}
{{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}
{{ $address->country }}</p>
                    <p class="text-sm text-gray-500 mt-1">Phone: {{ $address->phone }}</p>

                    <div class="flex items-center gap-3 mt-4 text-sm">
                        <a href="{{ route('addresses.edit', $address) }}" class="text-blue-600 hover:underline">Edit</a>
                        @unless($address->is_default)
                            <form action="{{ route('addresses.default', $address) }}" method="POST">
                                @csrf
                                <button class="text-gray-600 hover:underline">Set as Default</button>
                            </form>
                        @endunless
                        <form action="{{ route('addresses.destroy', $address) }}" method="POST" onsubmit="return confirm('Remove this address?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:underline">Remove</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
