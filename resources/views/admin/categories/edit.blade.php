@extends('layouts.admin')
@section('page-title', 'Edit Category')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-2xl">
    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
        @method('PUT')
        @include('admin.categories._form')
    </form>
</div>
@endsection
