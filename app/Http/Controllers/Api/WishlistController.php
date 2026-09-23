<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $ids = $request->user()->wishlistProductIds();

        return ProductResource::collection(
            Product::whereIn('id', $ids)->with('category')->latest('id')->paginate(12)
        );
    }

    public function toggle(Request $request, Product $product)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return response()->json(['message' => 'Admin accounts cannot use the wishlist.'], 422);
        }

        $existing = Wishlist::where('user_id', $user->id)->where('product_id', $product->id)->first();

        if ($existing) {
            $existing->delete();
            $wishlisted = false;
            $message = $product->name . ' removed from your wishlist.';
        } else {
            Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);
            $wishlisted = true;
            $message = $product->name . ' added to your wishlist.';
        }

        return response()->json([
            'wishlisted' => $wishlisted,
            'count' => $user->wishlists()->count(),
            'message' => $message,
        ]);
    }
}
