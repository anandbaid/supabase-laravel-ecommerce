<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::active()->with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
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

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $sort = $request->get('sort', 'latest');
        match ($sort) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'name' => $query->orderBy('name', 'asc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = \Illuminate\Support\Facades\Cache::remember('shop:categories:sidebar:v2', 600, function () {
            return Category::where('is_active', true)->topLevel()->with(['children' => function ($q) {
                $q->where('is_active', true);
            }])->get();
        });

        return view('shop.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);
        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)->get();

        $reviewSummary = $product->reviewSummary();

        $reviews = $product->reviews()
            ->with('user:id,name')
            ->latest()
            ->paginate(5, ['*'], 'reviews_page')
            ->withQueryString()
            ->fragment('reviews');

        $user = auth()->user();
        $canReview = $user && ! $user->isAdmin();
        $myReview = $canReview
            ? $product->reviews()->where('user_id', $user->id)->first()
            : null;

        return view('shop.show', compact('product', 'related', 'reviewSummary', 'reviews', 'canReview', 'myReview'));
    }
}
