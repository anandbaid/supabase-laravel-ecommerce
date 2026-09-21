@extends('layouts.app')
@section('title', 'Login - ShopEase')

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-white rounded-xl shadow-sm p-8">
        <h1 class="text-2xl font-bold mb-6 text-center">Welcome Back</h1>

        @if($errors->any())
            <div class="bg-red-100 text-red-700 text-sm px-4 py-2 rounded-lg mb-4">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Password</label>
                <input type="password" name="password" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember"> Remember me</label>
            <button class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700">Login</button>
        </form>
        <p class="text-center text-sm text-gray-500 mt-6">Don't have an account? <a href="{{ route('register') }}" class="text-blue-600 font-medium">Register</a></p>
    </div>
</div>
@endsection
