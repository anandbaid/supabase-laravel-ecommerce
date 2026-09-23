<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /** Create the customer's review, or update it (one per customer per product). */
    public function store(Request $request, string $slug)
    {
        $product = Product::active()->where('slug', $slug)->firstOrFail();
        $user = $request->user();

        if ($user->isAdmin()) {
            return response()->json(['message' => 'Admin accounts cannot post reviews.'], 403);
        }

        $data = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'title' => 'nullable|string|max:120',
            'body' => 'required|string|min:10|max:2000',
        ], [
            'rating.required' => 'Please choose a star rating.',
            'rating.between' => 'Please choose a star rating between 1 and 5.',
            'body.required' => 'Please write a few words about the product.',
            'body.min' => 'Your review should be at least 10 characters.',
        ]);

        // "Verified buyer" = has a non-cancelled order containing this product.
        $verified = OrderItem::where('product_id', $product->id)
            ->whereHas('order', fn ($q) => $q
                ->where('user_id', $user->id)
                ->where('status', '!=', 'cancelled'))
            ->exists();

        $review = Review::updateOrCreate(
            ['product_id' => $product->id, 'user_id' => $user->id],
            [
                'rating' => $data['rating'],
                'title' => $data['title'] ?? null,
                'body' => $data['body'],
                'is_verified_purchase' => $verified,
            ]
        );

        return response()->json([
            'message' => $review->wasRecentlyCreated ? 'Thanks! Your review is live.' : 'Your review was updated.',
            'review' => new ReviewResource($review->setRelation('user', $user)),
        ], $review->wasRecentlyCreated ? 201 : 200);
    }

    /** Customers can delete their own review; admins can delete any. */
    public function destroy(Request $request, Review $review)
    {
        abort_unless($request->user()->isAdmin() || $review->user_id === $request->user()->id, 403);

        $review->delete();

        return response()->json(['message' => 'Review deleted.']);
    }
}
