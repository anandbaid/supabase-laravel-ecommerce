@extends('layouts.admin')
@section('page-title', 'Add Coupon')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
    <form action="{{ route('admin.coupons.store') }}" method="POST">
        @include('admin.coupons._form')
    </form>
</div>
@endsection
