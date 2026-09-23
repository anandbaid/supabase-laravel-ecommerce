<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ReviewResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

/** Public, read-only catalog endpoints for the storefront. */
class CatalogController extends Controller
{
    /** Store-wide settings the storefront displays (shipping promise, tax rate). */
    public function settings()
    {
        return response()->json([
            'tax_rate' => Setting::taxRate(),
            'shipping_fee' => Setting::shippingFee(),
            'free_shipping_threshold' => Setting::freeShippingThreshold(),
            'max_qty_per_item' => \App\Services\CartPricing::MAX_QTY_PER_ITEM,
            'currency' => strtoupper((string) config('services.stripe.currency', 'usd')),
        ]);
    }

    /** Top-level categories with their active subcategories (header menu, shop filter). */
    public function categories()
    {
        $categories = Category::where('is_active', true)->topLevel()
            ->with(['children' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function home()
    {
        return response()->json([
            'categories' => CategoryResource::collection(Category::where('is_active', true)->take(8)->get()),
            'featured' => ProductResource::collection(Product::active()->where('is_featured', true)->latest()->take(6)->get()),
            'latest' => ProductResource::collection(Product::active()->latest()->take(8)->get()),
        ]);
    }

    public function products(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:150',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'sort' => 'nullable|in:latest,price_low,price_high,name',
            'per_page' => 'nullable|integer|min:1|max:48',
        ]);

        $query = Product::active()->with('category');

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $selected = Category::where('slug', $request->category)->first();
            if ($selected) {
                $categoryIds = $selected->isSubcategory() ? [$selected->id] : $selected->selfAndDescendantIds();
                $query->whereIn('category_id', $categoryIds);
            }
        }

        if ($request->boolean('deals')) {
            $query->whereNotNull('discount_price');
        }

        // Filter and sort on the price shoppers see (the sale price when set).
        $query->finalPriceBetween($request->input('min_price'), $request->input('max_price'));

        match ($request->get('sort', 'latest')) {
            'price_low' => $query->orderByFinalPrice('asc'),
            'price_high' => $query->orderByFinalPrice('desc'),
            'name' => $query->orderBy('name', 'asc'),
            default => $query->latest(),
        };

        return ProductResource::collection($query->paginate((int) $request->get('per_page', 12)));
    }

    /** Header search-as-you-type suggestions. */
    public function suggest(Request $request)
    {
        $term = trim((string) $request->get('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['data' => []]);
        }

        $products = Product::active()
            ->where('name', 'ilike', '%' . $term . '%')
            ->take(6)
            ->get();

        return ProductResource::collection($products);
    }

    public function product(Request $request, string $slug)
    {
        $product = Product::active()->where('slug', $slug)->with('category')->firstOrFail();

        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)->get();

        $user = $request->user();
        $myReview = $user && ! $user->isAdmin()
            ? $product->reviews()->where('user_id', $user->id)->first()
            : null;

        return response()->json([
            'product' => (new ProductResource($product))->detailed(),
            'related' => ProductResource::collection($related),
            'review_summary' => $product->reviewSummary(),
            'my_review' => $myReview ? new ReviewResource($myReview->setRelation('user', $user)) : null,
            'can_review' => (bool) ($user && ! $user->isAdmin()),
        ]);
    }

    public function reviews(Request $request, string $slug)
    {
        $product = Product::active()->where('slug', $slug)->firstOrFail();

        return ReviewResource::collection(
            $product->reviews()->with('user:id,name')->latest()->paginate(5)
        );
    }
}
