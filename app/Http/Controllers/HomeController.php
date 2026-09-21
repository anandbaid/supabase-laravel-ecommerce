<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)->take(8)->get();
        $featured = Product::active()->where('is_featured', true)->latest()->take(6)->get();
        $latest = Product::active()->latest()->take(8)->get();

        return view('home', compact('categories', 'featured', 'latest'));
    }
}
