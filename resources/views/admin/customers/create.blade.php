@extends('layouts.admin')
@section('page-title', 'Add Account')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-3xl">
    <div class="flex items-center gap-2 mb-5 pb-4 border-b">
        <i data-lucide="user-plus" class="w-5 h-5 text-blue-600"></i>
        <h2 class="font-semibold text-gray-800">Add Account</h2>
    </div>
    <form action="{{ route('admin.customers.store') }}" method="POST">
        @include('admin.customers._form')
    </form>
</div>
@endsection
