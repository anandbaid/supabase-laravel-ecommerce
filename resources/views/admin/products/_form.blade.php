@csrf
<div class="flex items-center gap-2 mb-5 pb-4 border-b">
    <i data-lucide="box" class="w-5 h-5 text-blue-600"></i>
    <h2 class="font-semibold text-gray-800">Product Information</h2>
</div>

<div class="grid md:grid-cols-2 gap-5">
    <div>
        <label class="text-sm font-medium flex items-center gap-1.5">
            <i data-lucide="tag" class="w-4 h-4 text-gray-400"></i> Product Name <span class="text-red-500">*</span>
        </label>
        <input id="product-name" type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
    </div>
    <div>
        <label class="text-sm font-medium flex items-center gap-1.5">
            <i data-lucide="folder" class="w-4 h-4 text-gray-400"></i> Category <span class="text-red-500">*</span>
        </label>
        <select id="product-category" name="category_id" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            <option value="">Select category</option>
            @foreach($categories->whereNull('parent_id') as $top)
                @php $subs = $categories->where('parent_id', $top->id); @endphp
                @if($subs->isNotEmpty())
                    <optgroup label="{{ $top->name }}">
                        <option value="{{ $top->id }}" @selected(old('category_id', $product->category_id ?? '') == $top->id)>{{ $top->name }} (general)</option>
                        @foreach($subs as $sub)
                            <option value="{{ $sub->id }}" @selected(old('category_id', $product->category_id ?? '') == $sub->id)>— {{ $sub->name }}</option>
                        @endforeach
                    </optgroup>
                @else
                    <option value="{{ $top->id }}" @selected(old('category_id', $product->category_id ?? '') == $top->id)>{{ $top->name }}</option>
                @endif
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium flex items-center gap-1.5">
            <i data-lucide="circle-dollar-sign" class="w-4 h-4 text-gray-400"></i> Price ($) <span class="text-red-500">*</span>
        </label>
        <input id="product-price" type="number" step="0.01" name="price" value="{{ old('price', $product->price ?? '') }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
    </div>
    <div>
        <label class="text-sm font-medium flex items-center gap-1.5">
            <i data-lucide="percent" class="w-4 h-4 text-gray-400"></i> Discount Price ($) <span class="text-gray-400 font-normal">— optional</span>
        </label>
        <input id="product-discount" type="number" step="0.01" name="discount_price" value="{{ old('discount_price', $product->discount_price ?? '') }}" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
    </div>
    <div>
        <label class="text-sm font-medium flex items-center gap-1.5">
            <i data-lucide="package" class="w-4 h-4 text-gray-400"></i> Stock Quantity <span class="text-red-500">*</span>
        </label>
        <input id="product-stock" type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
    </div>
    <div>
        <label class="text-sm font-medium flex items-center gap-1.5">
            <i data-lucide="image" class="w-4 h-4 text-gray-400"></i> Product Image
        </label>
        <label for="product-image-input" class="mt-1 flex items-center gap-2 border rounded-lg px-3 py-2 text-sm cursor-pointer hover:bg-gray-50">
            <span class="inline-flex items-center gap-1.5 bg-gray-100 px-2.5 py-1 rounded-md text-xs font-medium text-gray-700">
                <i data-lucide="upload" class="w-3.5 h-3.5"></i> Browse...
            </span>
            <span id="product-image-filename" class="text-gray-400 truncate">No file selected.</span>
        </label>
        <input id="product-image-input" type="file" name="image" accept="image/*" class="hidden">
        <p class="text-xs text-gray-400 mt-1">Recommended size: 800 x 800px (JPG, PNG)</p>
        @if(!empty($product) && $product->image)
            <img id="product-image-current" src="{{ $product->imageUrl() }}" class="w-16 h-16 object-contain mt-2 bg-gray-50 rounded-lg border">
        @endif
    </div>
</div>

<div class="mt-5">
    <label class="text-sm font-medium flex items-center gap-1.5">
        <i data-lucide="images" class="w-4 h-4 text-gray-400"></i> Gallery Images <span class="text-gray-400 font-normal">— optional, shown as a slider on the product page</span>
    </label>

    <label for="gallery-input" class="mt-1 flex flex-col items-center justify-center gap-2 border-2 border-dashed rounded-xl px-4 py-8 text-center cursor-pointer hover:bg-gray-50 hover:border-blue-400 transition">
        <i data-lucide="upload-cloud" class="w-7 h-7 text-blue-500"></i>
        <span class="text-sm text-gray-600">Drag &amp; drop images here or click to browse</span>
        <span class="text-xs text-gray-400">You can select multiple images. New uploads are added to the existing gallery below.</span>
        <span class="inline-flex items-center gap-1.5 bg-blue-600 text-white px-4 py-1.5 rounded-lg text-xs font-medium mt-1">
            <i data-lucide="folder-open" class="w-3.5 h-3.5"></i> Choose Files
        </span>
    </label>
    <input id="gallery-input" type="file" name="gallery[]" accept="image/*" multiple class="hidden">

    <div id="gallery-new-preview" class="flex flex-wrap gap-3 mt-3"></div>

    @if(!empty($product) && $product->gallery && count($product->gallery))
        <div id="gallery-existing" class="flex flex-wrap gap-3 mt-3">
            @foreach($product->gallery as $path)
                @php
                    $galleryUrl = null;
                    try {
                        $galleryUrl = \Illuminate\Support\Facades\Storage::disk('s3')->url($path);
                    } catch (\Throwable $e) {
                        // leave as null; skip broken thumbnail rather than fail the page
                    }
                @endphp
                @if($galleryUrl)
                    <div class="gallery-existing-item relative w-20 h-20 rounded-lg overflow-hidden border bg-gray-50">
                        <img src="{{ $galleryUrl }}" class="w-full h-full object-cover">
                        <div class="gallery-remove-overlay absolute inset-0 bg-black/0 hidden items-center justify-center flex-col gap-0.5 transition">
                            <i data-lucide="x-circle" class="w-5 h-5 text-white"></i>
                            <span class="text-white text-[10px] font-medium">Removed</span>
                        </div>
                        <button type="button" class="gallery-remove-btn absolute top-1 right-1 w-5 h-5 rounded-full bg-white/90 hover:bg-red-600 hover:text-white text-gray-600 flex items-center justify-center shadow" title="Remove image">
                            <i data-lucide="x" class="w-3 h-3"></i>
                        </button>
                        <input type="checkbox" name="remove_gallery[]" value="{{ $path }}" class="hidden">
                    </div>
                @endif
            @endforeach
        </div>
        <p class="text-xs text-gray-400 mt-2">Click the <i data-lucide="x" class="w-3 h-3 inline"></i> on an image to mark it for removal, then save. Click again to undo.</p>
    @endif
</div>

<div class="mt-5">
    <label class="text-sm font-medium flex items-center gap-1.5">
        <i data-lucide="align-left" class="w-4 h-4 text-gray-400"></i> Description
    </label>
    <div class="border rounded-lg mt-1 overflow-hidden">
        <div class="flex items-center gap-3 px-3 py-2 border-b bg-gray-50 text-gray-500">
            <i data-lucide="bold" class="w-4 h-4"></i>
            <i data-lucide="italic" class="w-4 h-4"></i>
            <i data-lucide="underline" class="w-4 h-4"></i>
            <i data-lucide="list" class="w-4 h-4"></i>
            <i data-lucide="list-ordered" class="w-4 h-4"></i>
            <i data-lucide="link" class="w-4 h-4"></i>
            <i data-lucide="image" class="w-4 h-4"></i>
            <i data-lucide="code" class="w-4 h-4"></i>
        </div>
        <textarea id="product-description" name="description" rows="4" class="w-full px-3 py-2 text-sm outline-none">{{ old('description', $product->description ?? '') }}</textarea>
    </div>
</div>

<div class="flex items-center gap-6 mt-5">
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured ?? false))>
        <i data-lucide="star" class="w-4 h-4 text-gray-400"></i> Featured Product
    </label>
    <label class="flex items-center gap-2 text-sm">
        <input id="product-active-toggle" type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))>
        Active
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-blue-700 transition">
        <i data-lucide="save" class="w-4 h-4"></i> {{ isset($product) ? 'Update Product' : 'Create Product' }}
    </button>
    <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 text-gray-500 text-sm px-4 py-2.5 hover:text-gray-700">
        <i data-lucide="x" class="w-4 h-4"></i> Cancel
    </a>
</div>

<script>
(function () {
    var imageInput = document.getElementById('product-image-input');
    var filenameLabel = document.getElementById('product-image-filename');
    var currentImg = document.getElementById('product-image-current');
    var previewImg = document.getElementById('sidebar-image-preview');
    var previewPlaceholder = document.getElementById('sidebar-image-placeholder');

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            var file = imageInput.files && imageInput.files[0];
            if (!file) {
                if (filenameLabel) filenameLabel.textContent = 'No file selected.';
                return;
            }
            if (filenameLabel) filenameLabel.textContent = file.name;

            var reader = new FileReader();
            reader.onload = function (e) {
                if (currentImg) currentImg.src = e.target.result;
                if (previewImg) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                }
                if (previewPlaceholder) previewPlaceholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        });
    }

    var galleryInput = document.getElementById('gallery-input');
    var galleryPreview = document.getElementById('gallery-new-preview');

    function renderGalleryPreview() {
        if (!galleryInput || !galleryPreview) return;
        galleryPreview.innerHTML = '';
        Array.from(galleryInput.files || []).forEach(function (file, index) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var wrap = document.createElement('div');
                wrap.className = 'relative w-20 h-20 rounded-lg overflow-hidden border bg-gray-50';
                wrap.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">' +
                    '<span class="absolute bottom-0 inset-x-0 bg-black/50 text-white text-[10px] text-center py-0.5">New</span>' +
                    '<button type="button" data-index="' + index + '" class="gallery-new-remove-btn absolute top-1 right-1 w-5 h-5 rounded-full bg-white/90 hover:bg-red-600 hover:text-white text-gray-600 flex items-center justify-center shadow" title="Remove"><i data-lucide="x" class="w-3 h-3"></i></button>';
                galleryPreview.appendChild(wrap);
                if (window.lucide) lucide.createIcons();
            };
            reader.readAsDataURL(file);
        });
    }

    if (galleryInput && galleryPreview) {
        galleryInput.addEventListener('change', renderGalleryPreview);

        // Remove a not-yet-uploaded file from the pending selection before submit.
        galleryPreview.addEventListener('click', function (e) {
            var btn = e.target.closest('.gallery-new-remove-btn');
            if (!btn) return;
            var indexToRemove = parseInt(btn.getAttribute('data-index'), 10);
            var dt = new DataTransfer();
            Array.from(galleryInput.files || []).forEach(function (file, i) {
                if (i !== indexToRemove) dt.items.add(file);
            });
            galleryInput.files = dt.files;
            renderGalleryPreview();
        });
    }

    // Mark/unmark an already-uploaded gallery image for removal on save.
    var existingGallery = document.getElementById('gallery-existing');
    if (existingGallery) {
        existingGallery.addEventListener('click', function (e) {
            var btn = e.target.closest('.gallery-remove-btn');
            if (!btn) return;
            var item = btn.closest('.gallery-existing-item');
            var checkbox = item.querySelector('input[type="checkbox"]');
            var overlay = item.querySelector('.gallery-remove-overlay');
            checkbox.checked = !checkbox.checked;
            if (checkbox.checked) {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex', 'bg-black/60');
                item.classList.add('opacity-60');
                btn.classList.add('bg-red-600', 'text-white');
                btn.title = 'Undo remove';
            } else {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex', 'bg-black/60');
                item.classList.remove('opacity-60');
                btn.classList.remove('bg-red-600', 'text-white');
                btn.title = 'Remove image';
            }
        });
    }

    // live sync of name / price / discount / stock into sidebar quick info + preview card
    var nameInput = document.getElementById('product-name');
    var priceInput = document.getElementById('product-price');
    var discountInput = document.getElementById('product-discount');
    var stockInput = document.getElementById('product-stock');
    var categorySelect = document.getElementById('product-category');
    var activeToggle = document.getElementById('product-active-toggle');

    var sNameEls = document.querySelectorAll('[data-preview="name"]');
    var sPriceEls = document.querySelectorAll('[data-preview="price"]');
    var sDiscountEls = document.querySelectorAll('[data-preview="discount"]');
    var sStockEls = document.querySelectorAll('[data-preview="stock"]');
    var sCategoryEls = document.querySelectorAll('[data-preview="category"]');
    var sStatusEls = document.querySelectorAll('[data-preview="status"]');

    function setText(list, value) {
        list.forEach(function (el) { el.textContent = value; });
    }

    function refreshPreview() {
        if (nameInput) setText(sNameEls, nameInput.value || 'Untitled product');
        if (priceInput) setText(sPriceEls, priceInput.value ? '$' + parseFloat(priceInput.value).toFixed(2) : '$0.00');
        if (discountInput) setText(sDiscountEls, discountInput.value ? '$' + parseFloat(discountInput.value).toFixed(2) : '');
        if (stockInput) setText(sStockEls, stockInput.value || '0');
        if (categorySelect) setText(sCategoryEls, categorySelect.options[categorySelect.selectedIndex] ? categorySelect.options[categorySelect.selectedIndex].text : 'Select category');
        if (activeToggle) setText(sStatusEls, activeToggle.checked ? 'Active' : 'Inactive');
    }

    [nameInput, priceInput, discountInput, stockInput, categorySelect, activeToggle].forEach(function (el) {
        if (el) el.addEventListener('input', refreshPreview);
        if (el) el.addEventListener('change', refreshPreview);
    });

    refreshPreview();
    if (window.lucide) lucide.createIcons();
})();
</script>