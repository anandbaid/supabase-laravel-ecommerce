@extends('layouts.admin')
@section('page-title', 'Add Category')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
        @include('admin.categories._form')
    </form>
</div>
@endsection
