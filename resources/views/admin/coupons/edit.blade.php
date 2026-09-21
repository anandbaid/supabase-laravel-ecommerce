@extends('layouts.admin')
@section('page-title', 'Edit Coupon')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
    <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST">
        @method('PUT')
        @include('admin.coupons._form')
    </form>
</div>
@endsection
