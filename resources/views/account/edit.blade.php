@extends('layouts.account')

@section('account-content')
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-bold text-slate-900 mb-5 flex items-center gap-2">
            <i data-lucide="user-circle" class="w-5 h-5 text-blue-600"></i> Account Details
        </h2>
        <form action="{{ route('account.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PATCH')

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="text-sm font-medium flex items-center gap-1.5">
                        <i data-lucide="user" class="w-4 h-4 text-gray-400"></i> Full Name
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium flex items-center gap-1.5">
                        <i data-lucide="mail" class="w-4 h-4 text-gray-400"></i> Email
                    </label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium flex items-center gap-1.5">
                        <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i> Phone <span class="text-gray-400 font-normal">— optional</span>
                    </label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-gray-400"></i> Address <span class="text-gray-400 font-normal">— optional</span>
                    </label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
            </div>

            <div class="pt-5 border-t">
                <h3 class="text-sm font-semibold flex items-center gap-1.5 mb-1">
                    <i data-lucide="lock" class="w-4 h-4 text-gray-400"></i> Change Password
                </h3>
                <p class="text-xs text-gray-400 mb-3">Leave blank to keep your current password.</p>
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="text-sm font-medium">New Password</label>
                        <input type="password" name="password" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-blue-700 transition">
                    <i data-lucide="save" class="w-4 h-4"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
@endsection
