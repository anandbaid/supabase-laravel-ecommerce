<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        // Categories and the featured/latest product lists change rarely
        // (only when an admin edits a product/category), but were being
        // re-queried from the remote DB on every single homepage visit.
        // Caching for 5 minutes cuts that to near-zero for normal traffic,
        // and any admin edit that needs to show up sooner can call
        // Cache::forget() on these keys (or just wait out the 5 minutes).
        $categories = Cache::remember('home.categories', 300, function () {
            return Category::where('is_active', true)->take(8)->get();
        });

        $featured = Cache::remember('home.featured_products', 300, function () {
            return Product::active()->where('is_featured', true)->latest()->take(6)->get();
        });

        $latest = Cache::remember('home.latest_products', 300, function () {
            return Product::active()->latest()->take(8)->get();
        });

        return view('home', compact('categories', 'featured', 'latest'));
    }
}
