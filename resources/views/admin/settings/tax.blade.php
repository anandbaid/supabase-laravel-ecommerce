@extends('layouts.admin')
@section('page-title', 'Tax Settings')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-xl">
    <div class="flex items-center gap-2 mb-5 pb-4 border-b">
        <i data-lucide="percent" class="w-5 h-5 text-blue-600"></i>
        <h2 class="font-semibold text-gray-800">Store Tax Rate</h2>
    </div>

    <form action="{{ route('admin.settings.tax.update') }}" method="POST">
        @csrf
        @method('PUT')
        <label class="text-sm font-medium">Tax Rate (%)</label>
        <input type="number" step="0.01" min="0" max="100" name="tax_rate" value="{{ old('tax_rate', $taxRate) }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
        <p class="text-xs text-gray-400 mt-1">Applied to every order's subtotal (after any coupon discount) at checkout. Set to 0 to disable tax.</p>

        <div class="mt-6">
            <button class="bg-blue-600 text-white px-6 py-2.5 rounded-lg font-medium">Save Tax Rate</button>
        </div>
    </form>
</div>
@endsection
