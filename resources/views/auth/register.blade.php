@extends('layouts.app')
@section('title', 'Register - ShopEase')

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-white rounded-xl shadow-sm p-8">
        <h1 class="text-2xl font-bold mb-6 text-center">Create Account</h1>

        @if($errors->any())
            <div class="bg-red-100 text-red-700 text-sm px-4 py-2 rounded-lg mb-4">
                <ul class="list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Password</label>
                <input type="password" name="password" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Confirm Password</label>
                <input type="password" name="password_confirmation" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
            </div>
            <button class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700">Register</button>
        </form>
        <p class="text-center text-sm text-gray-500 mt-6">Already have an account? <a href="{{ route('login') }}" class="text-blue-600 font-medium">Login</a></p>
    </div>
</div>
@endsection
