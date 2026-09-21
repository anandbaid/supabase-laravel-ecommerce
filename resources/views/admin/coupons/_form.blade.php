@csrf

<div class="grid md:grid-cols-2 gap-5">
    <div>
        <label class="text-sm font-medium">Coupon Code</label>
        <input type="text" name="code" value="{{ old('code', $coupon->code ?? '') }}" required placeholder="e.g. SAVE20" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm uppercase">
    </div>
    <div>
        <label class="text-sm font-medium">Discount Type</label>
        <select name="type" id="coupon-type" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
            <option value="percent" @selected(old('type', $coupon->type ?? 'percent') === 'percent')>Percentage off</option>
            <option value="fixed" @selected(old('type', $coupon->type ?? '') === 'fixed')>Fixed amount off</option>
        </select>
    </div>
    <div>
        <label class="text-sm font-medium">Value</label>
        <input type="number" step="0.01" min="0.01" name="value" value="{{ old('value', $coupon->value ?? '') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
        <p class="text-xs text-gray-400 mt-1">A percentage (0–100) or a fixed dollar amount, depending on the type above.</p>
    </div>
    <div>
        <label class="text-sm font-medium">Max Discount Amount <span class="text-gray-400 font-normal">— optional, percent only</span></label>
        <input type="number" step="0.01" min="0" name="max_discount_amount" value="{{ old('max_discount_amount', $coupon->max_discount_amount ?? '') }}" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">Minimum Order Amount</label>
        <input type="number" step="0.01" min="0" name="min_order_amount" value="{{ old('min_order_amount', $coupon->min_order_amount ?? 0) }}" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">Usage Limit <span class="text-gray-400 font-normal">— optional, blank = unlimited</span></label>
        <input type="number" min="1" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">Expiry Date <span class="text-gray-400 font-normal">— optional</span></label>
        <input type="date" name="expires_at" value="{{ old('expires_at', isset($coupon) && $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : '') }}" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
    </div>
</div>

<label class="flex items-center gap-2 text-sm mt-5">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->is_active ?? true))> Active
</label>

<div class="mt-6">
    <button class="bg-blue-600 text-white px-6 py-2.5 rounded-lg font-medium">{{ isset($coupon) ? 'Update Coupon' : 'Create Coupon' }}</button>
    <a href="{{ route('admin.coupons.index') }}" class="ml-3 text-gray-500 text-sm">Cancel</a>
</div>
