@extends('layouts.app')
@section('title', "My Account - Let's Shop")

@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">My Account</h1>

    <div class="grid md:grid-cols-[220px_1fr] gap-6 items-start">
        @include('account.sidebar')

        <div>
            @yield('account-content')
        </div>
    </div>
</div>
@endsection
