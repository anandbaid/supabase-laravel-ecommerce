@csrf

<div class="grid md:grid-cols-2 gap-5">
    <div>
        <label class="text-sm font-medium flex items-center gap-1.5">
            <i data-lucide="user" class="w-4 h-4 text-gray-400"></i> Full Name <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" value="{{ old('name', $customer->name ?? '') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="text-sm font-medium flex items-center gap-1.5">
            <i data-lucide="mail" class="w-4 h-4 text-gray-400"></i> Email <span class="text-red-500">*</span>
        </label>
        <input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="text-sm font-medium flex items-center gap-1.5">
            <i data-lucide="shield" class="w-4 h-4 text-gray-400"></i> Role <span class="text-red-500">*</span>
        </label>
        <select name="role" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            <option value="customer" @selected(old('role', $customer->role ?? 'customer') == 'customer')>Customer</option>
            <option value="admin" @selected(old('role', $customer->role ?? '') == 'admin')>Admin</option>
        </select>
        @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="text-sm font-medium flex items-center gap-1.5">
            <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i> Phone <span class="text-gray-400 font-normal">— optional</span>
        </label>
        <input type="text" name="phone" value="{{ old('phone', $customer->phone ?? '') }}" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
    </div>
    <div class="md:col-span-2">
        <label class="text-sm font-medium flex items-center gap-1.5">
            <i data-lucide="map-pin" class="w-4 h-4 text-gray-400"></i> Address <span class="text-gray-400 font-normal">— optional</span>
        </label>
        <input type="text" name="address" value="{{ old('address', $customer->address ?? '') }}" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
    </div>
</div>

<div class="mt-5 pt-5 border-t">
    <h3 class="text-sm font-semibold flex items-center gap-1.5 mb-1">
        <i data-lucide="lock" class="w-4 h-4 text-gray-400"></i> {{ isset($customer) ? 'Change Password' : 'Set Password' }} {{ isset($customer) ? '' : '*' }}
    </h3>
    @if(isset($customer))
        <p class="text-xs text-gray-400 mb-3">Leave blank to keep their current password.</p>
    @endif
    <div class="grid md:grid-cols-2 gap-5">
        <div>
            <input type="password" name="password" {{ isset($customer) ? '' : 'required' }} placeholder="{{ isset($customer) ? 'New password' : 'Password' }}" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <input type="password" name="password_confirmation" {{ isset($customer) ? '' : 'required' }} placeholder="Confirm password" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
        </div>
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-blue-700 transition">
        <i data-lucide="save" class="w-4 h-4"></i> {{ isset($customer) ? 'Update Account' : 'Create Account' }}
    </button>
    <a href="{{ route('admin.customers.index') }}" class="inline-flex items-center gap-2 text-gray-500 text-sm px-4 py-2.5 hover:text-gray-700">
        <i data-lucide="x" class="w-4 h-4"></i> Cancel
    </a>
</div>
