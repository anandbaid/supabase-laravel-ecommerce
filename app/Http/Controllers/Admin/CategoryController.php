<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageResizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->withCount('products')->latest()->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = Category::topLevel()->orderBy('name')->get();
        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            try {
                $variants = ImageResizer::resizeAndStoreVariants($request->file('image'), 'categories', 's3');
                $data['image_small'] = $variants['small'];
                $data['image_medium'] = $variants['medium'];
                $data['image_large'] = $variants['large'];
                $data['image'] = $variants['large'];
            } catch (Throwable $e) {
                Log::error('Category image upload failed: ' . $e->getMessage(), ['exception' => $e]);
                return back()->withInput()->with('error', 'Category image failed to upload. Please try again.');
            }
        }

        try {
            Category::create($data);
            \Illuminate\Support\Facades\Cache::forget('shop:categories:sidebar:v2');
        } catch (Throwable $e) {
            Log::error('Category create failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->with('error', 'Could not save the category. Please try again.');
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        $parentCategories = Category::topLevel()->where('id', '!=', $category->id)->orderBy('name')->get();
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => ['nullable', 'exists:categories,id', Rule::notIn([$category->id])],
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'is_active' => 'nullable|boolean',
        ]);

        // A category that already has children can't become someone else's
        // subcategory — keep the hierarchy to two levels.
        if (!empty($data['parent_id']) && $category->children()->exists()) {
            return back()->withInput()->with('error', 'This category has subcategories, so it can\'t be made a subcategory itself.');
        }

        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $oldSmall = $category->image_small;
            $oldMedium = $category->image_medium;
            $oldLarge = $category->image_large;

            try {
                $variants = ImageResizer::resizeAndStoreVariants($request->file('image'), 'categories', 's3');
                $data['image_small'] = $variants['small'];
                $data['image_medium'] = $variants['medium'];
                $data['image_large'] = $variants['large'];
                $data['image'] = $variants['large'];
            } catch (Throwable $e) {
                Log::error('Category image upload failed: ' . $e->getMessage(), ['exception' => $e]);
                return back()->withInput()->with('error', 'Category image failed to upload. Please try again.');
            }

            ImageResizer::deleteVariants($oldSmall, $oldMedium, $oldLarge, 's3');
        }

        try {
            $category->update($data);
            \Illuminate\Support\Facades\Cache::forget('shop:categories:sidebar:v2');
        } catch (Throwable $e) {
            Log::error('Category update failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->with('error', 'Could not update the category. Please try again.');
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        ImageResizer::deleteVariants($category->image_small, $category->image_medium, $category->image_large, 's3');

        try {
            $category->delete();
        \Illuminate\Support\Facades\Cache::forget('shop:categories:sidebar:v2');
        } catch (Throwable $e) {
            Log::error('Category delete failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Could not delete the category. Please try again.');
        }

        return back()->with('success', 'Category deleted.');
    }
}
