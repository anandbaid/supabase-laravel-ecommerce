@extends('layouts.app')
@section('title', "Contact Us - Let's Shop")

@section('content')
<div class="max-w-5xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold mb-2">Contact Us</h1>
    <p class="text-gray-500 mb-8">We're here to help. Reach out to us anytime.</p>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3 mb-6">{{ session('success') }}</div>
    @endif

    <div class="grid md:grid-cols-2 gap-8">
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-5">
            <div class="flex items-start gap-3">
                <i data-lucide="map-pin" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                <div>
                    <div class="font-semibold text-sm">Address</div>
                    <div class="text-sm text-gray-500">123 Shopping Street, Kolkata, India</div>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <i data-lucide="mail" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                <div>
                    <div class="font-semibold text-sm">Email</div>
                    <div class="text-sm text-gray-500">support@letsshop.com</div>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <i data-lucide="phone" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                <div>
                    <div class="font-semibold text-sm">Phone</div>
                    <div class="text-sm text-gray-500">+91 98765 43210</div>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <i data-lucide="clock" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                <div>
                    <div class="font-semibold text-sm">Business Hours</div>
                    <div class="text-sm text-gray-500">Mon – Sat, 9:00 AM – 8:00 PM</div>
                </div>
            </div>
        </div>

        <form action="{{ route('static.contact.submit') }}" method="POST" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Name</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium">Email</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
            </div>
            <div>
                <label class="text-sm font-medium">Message</label>
                <textarea name="message" rows="5" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">{{ old('message') }}</textarea>
            </div>
            @error('name') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            @error('email') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            @error('message') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            <button class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700">Send Message</button>
        </form>
    </div>
</div>
@endsection
