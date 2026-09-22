<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /** The logged-in customer's saved products. */
    public function index(Request $request)
    {
        $ids = $request->user()->wishlistProductIds();

        $products = Product::whereIn('id', $ids)
            ->with('category')
            ->latest('id')
            ->paginate(12);

        return view('account.wishlist', ['products' => $products, 'wishlistIds' => $ids]);
    }

    /**
     * Add or remove a product from the current user's wishlist. Called via
     * fetch() from the heart button on product cards and the product page.
     */
    public function toggle(Request $request, Product $product)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Please log in to save items to your wishlist.',
                'redirect' => route('login'),
            ], 401);
        }

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
