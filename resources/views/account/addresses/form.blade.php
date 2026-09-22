@extends('layouts.app')
@section('title', ($address ?? null ? 'Edit Address' : 'Add Address') . ' - ShopEase')

@section('content')
<div class="max-w-xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">{{ $address ?? null ? 'Edit Address' : 'Add New Address' }}</h1>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form action="{{ $address ?? null ? route('addresses.update', $address) : route('addresses.store') }}" method="POST" class="space-y-4">
            @csrf
            @if($address ?? null)
                @method('PUT')
            @endif

            <div>
                <label class="text-sm font-medium">Address Label</label>
                <select name="label" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
                    @foreach(['Home', 'Work', 'Other'] as $label)
                        <option value="{{ $label }}" @selected(old('label', $address->label ?? 'Home') === $label)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Full Name</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $address->full_name ?? auth()->user()->name ?? '') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $address->phone ?? '') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
                </div>
            </div>

            <div>
                <label class="text-sm font-medium">Address Line 1</label>
                <input type="text" name="line1" value="{{ old('line1', $address->line1 ?? '') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Address Line 2 <span class="text-gray-400 font-normal">— optional</span></label>
                <input type="text" name="line2" value="{{ old('line2', $address->line2 ?? '') }}" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">City</label>
                    <input type="text" name="city" value="{{ old('city', $address->city ?? '') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">State</label>
                    <input type="text" name="state" value="{{ old('state', $address->state ?? '') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Postal Code</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $address->postal_code ?? '') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Country</label>
                    <input type="text" name="country" value="{{ old('country', $address->country ?? 'India') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $address->is_default ?? false))> Make this my default address
            </label>

            <div class="flex items-center gap-3 pt-2">
                <button class="bg-blue-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-blue-700">Save Address</button>
                <a href="{{ route('addresses.index') }}" class="text-gray-500 text-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
