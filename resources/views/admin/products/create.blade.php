@extends('layouts.admin')
@section('page-title', 'Add Product')

@section('content')
<div class="max-w-6xl">
    <div class="flex items-center justify-between mb-5">
        <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 bg-white border rounded-lg px-3 py-1.5 hover:bg-gray-50">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Products
        </a>
        <span data-preview="status" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1 rounded-full bg-green-100 text-green-700">
            <i data-lucide="circle" class="w-2.5 h-2.5 fill-current"></i> Active
        </span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Add Product</h1>
        <p class="text-sm text-gray-400">Fill in the details to add a new product to your store.</p>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 items-start">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @include('admin.products._form')
            </form>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-center gap-2 mb-3">
                    <i data-lucide="eye" class="w-4 h-4 text-blue-600"></i>
                    <h3 class="font-semibold text-sm text-gray-800">Product Image Preview</h3>
                </div>
                <div class="rounded-lg overflow-hidden bg-gray-900 aspect-video flex items-center justify-center">
                    <img id="sidebar-image-preview" src="" class="w-full h-full object-cover hidden">
                    <i id="sidebar-image-placeholder" data-lucide="image" class="w-10 h-10 text-gray-600"></i>
                </div>
                <p data-preview="name" class="font-semibold mt-3">Untitled product</p>
                <p class="mt-1">
                    <span data-preview="price" class="font-bold text-gray-800">$0.00</span>
                    <span data-preview="discount" class="text-gray-400 line-through text-sm ml-1"></span>
                </p>
                <span class="inline-flex items-center gap-1 mt-2 text-xs font-medium px-2.5 py-1 rounded-full bg-green-100 text-green-700">
                    <i data-lucide="circle" class="w-2 h-2 fill-current"></i> In Stock
                </span>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-center gap-2 mb-3">
                    <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                    <h3 class="font-semibold text-sm text-gray-800">Product Quick Info</h3>
                </div>
                <div class="space-y-4 text-sm">
                    <div class="flex items-start gap-3">
                        <i data-lucide="folder" class="w-4 h-4 text-gray-400 mt-0.5"></i>
                        <div>
                            <div class="text-gray-400 text-xs">Category</div>
                            <div data-preview="category" class="font-medium">Select category</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i data-lucide="package" class="w-4 h-4 text-gray-400 mt-0.5"></i>
                        <div>
                            <div class="text-gray-400 text-xs">Stock Quantity</div>
                            <div data-preview="stock" class="font-medium">0</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i data-lucide="circle" class="w-4 h-4 text-green-500 fill-current mt-0.5"></i>
                        <div>
                            <div class="text-gray-400 text-xs">Status</div>
                            <div data-preview="status" class="font-medium text-green-600">Active</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="lightbulb" class="w-4 h-4 text-blue-600"></i>
                    <h3 class="font-semibold text-sm text-gray-800">Tips</h3>
                </div>
                <p class="text-sm text-blue-800">Make sure to add high-quality images and a clear description to get better sales.</p>
            </div>
        </div>
    </div>
</div>
@endsection