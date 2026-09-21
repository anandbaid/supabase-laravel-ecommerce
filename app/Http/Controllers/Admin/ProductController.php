<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageResizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            try {
                $variants = ImageResizer::resizeAndStoreVariants($request->file('image'), 'products', 's3');
                $data['image_small'] = $variants['small'];
                $data['image_medium'] = $variants['medium'];
                $data['image_large'] = $variants['large'];
                $data['image'] = $variants['large'];
            } catch (Throwable $e) {
                Log::error('Product image upload failed: ' . $e->getMessage(), ['exception' => $e]);
                return back()->withInput()->with('error', 'Product image failed to upload. Please try again.');
            }
        }

        $data['gallery'] = $this->resolveGallery($request, null);

        try {
            Product::create($data);
        } catch (Throwable $e) {
            Log::error('Product create failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->with('error', 'Could not save the product. Please try again.');
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product->id);

        if ($request->hasFile('image')) {
            $oldSmall = $product->image_small;
            $oldMedium = $product->image_medium;
            $oldLarge = $product->image_large;

            try {
                $variants = ImageResizer::resizeAndStoreVariants($request->file('image'), 'products', 's3');
                $data['image_small'] = $variants['small'];
                $data['image_medium'] = $variants['medium'];
                $data['image_large'] = $variants['large'];
                $data['image'] = $variants['large'];
            } catch (Throwable $e) {
                Log::error('Product image upload failed: ' . $e->getMessage(), ['exception' => $e]);
                return back()->withInput()->with('error', 'Product image failed to upload. Please try again.');
            }

            // Clean up the old variants only after the new ones uploaded successfully.
            ImageResizer::deleteVariants($oldSmall, $oldMedium, $oldLarge, 's3');
        }

        $data['gallery'] = $this->resolveGallery($request, $product);

        try {
            $product->update($data);
        } catch (Throwable $e) {
            Log::error('Product update failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->with('error', 'Could not update the product. Please try again.');
        }

        return redirect()->back()->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        ImageResizer::deleteVariants($product->image_small, $product->image_medium, $product->image_large, 's3');
        ImageResizer::deletePaths($product->gallery ?? [], 's3');

        try {
            $product->delete();
        } catch (Throwable $e) {
            Log::error('Product delete failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Could not delete the product. Please try again.');
        }

        return back()->with('success', 'Product deleted.');
    }

    private function validated(Request $request, ?int $productId = null): array
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|max:4096',
            'gallery' => 'nullable|array',
            'gallery.*' => 'nullable|image|max:4096',
            'remove_gallery' => 'nullable|array',
            'remove_gallery.*' => 'nullable|string',
        ]);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    /**
     * Upload any new gallery files and merge them with the product's
     * existing gallery, minus anything the admin checked for removal.
     * Non-fatal: if a single gallery file fails to resize/upload, it's
     * skipped and logged rather than failing the whole save.
     */
    private function resolveGallery(Request $request, ?Product $product): array
    {
        $existing = $product?->gallery ?? [];
        $toRemove = $request->input('remove_gallery', []);
        $kept = array_values(array_diff($existing, $toRemove));

        if (! empty($toRemove)) {
            ImageResizer::deletePaths($toRemove, 's3');
        }

        $uploaded = [];
        foreach ($request->file('gallery', []) as $file) {
            if (! $file) {
                continue;
            }
            try {
                $uploaded[] = ImageResizer::resizeAndStoreSingle($file, 'products/gallery', 's3');
            } catch (Throwable $e) {
                Log::error('Gallery image upload failed, skipping this image: ' . $e->getMessage(), ['exception' => $e]);
            }
        }

        return array_values(array_unique(array_merge($kept, $uploaded)));
    }
}