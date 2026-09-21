@csrf
<div class="grid md:grid-cols-2 gap-5">
    <div>
        <label class="text-sm font-medium">Category Name</label>
        <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
    </div>
    <div>
        <label class="text-sm font-medium">Parent Category <span class="text-gray-400 font-normal">— leave blank for a top-level category</span></label>
        <select name="parent_id" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
            <option value="">None (top-level category)</option>
            @foreach($parentCategories as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id ?? '') == $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="grid md:grid-cols-2 gap-5 mt-5">
    <div>
        <label class="text-sm font-medium">Image</label>
        <input type="file" name="image" accept="image/*" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
        @if(!empty($category) && $category->image)
            <img src="{{ $category->imageUrl() }}" class="w-16 h-16 object-contain mt-2 bg-gray-50 rounded-lg">
        @endif
    </div>
</div>

<div class="mt-5">
    <label class="text-sm font-medium">Description</label>
    <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">{{ old('description', $category->description ?? '') }}</textarea>
</div>

<label class="flex items-center gap-2 text-sm mt-5">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))> Active
</label>

<div class="mt-6">
    <button class="bg-blue-600 text-white px-6 py-2.5 rounded-lg font-medium">{{ isset($category) ? 'Update Category' : 'Create Category' }}</button>
    <a href="{{ route('admin.categories.index') }}" class="ml-3 text-gray-500 text-sm">Cancel</a>
</div>
