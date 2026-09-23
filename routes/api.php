<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront JSON API (v1)
|--------------------------------------------------------------------------
| Consumed by the Next.js storefront in /storefront. The admin panel stays on
| the Blade routes in web.php. Signed-in requests carry the customer's
| Supabase access token as a Bearer token (see AuthenticateSupabaseToken).
*/
Route::prefix('v1')->group(function () {
    // Catalog — public; a valid token (if sent) personalises the product page.
    Route::get('settings', [CatalogController::class, 'settings']);
    Route::get('home', [CatalogController::class, 'home']);
    Route::get('categories', [CatalogController::class, 'categories']);
    Route::get('products', [CatalogController::class, 'products']);
    Route::get('products/suggest', [CatalogController::class, 'suggest']);
    Route::get('products/{slug}', [CatalogController::class, 'product'])->middleware('supabase.auth:optional');
    Route::get('products/{slug}/reviews', [CatalogController::class, 'reviews']);

    Route::post('cart/quote', [CartController::class, 'quote']);

    Route::post('newsletter', [ContactController::class, 'subscribe'])->middleware('throttle:10,1');
    Route::post('contact', [ContactController::class, 'contact'])->middleware('throttle:5,1');

    // Auth — rate limited against password guessing.
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('auth/login', [AuthController::class, 'login']);
        Route::post('auth/register', [AuthController::class, 'register']);
    });
    Route::post('auth/refresh', [AuthController::class, 'refresh'])->middleware('throttle:30,1');

    Route::middleware('supabase.auth')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('me', [AccountController::class, 'me']);
        Route::patch('me', [AccountController::class, 'update']);
        Route::get('account/dashboard', [AccountController::class, 'dashboard']);

        Route::get('addresses', [AddressController::class, 'index']);
        Route::post('addresses', [AddressController::class, 'store']);
        Route::get('addresses/{address}', [AddressController::class, 'show']);
        Route::put('addresses/{address}', [AddressController::class, 'update']);
        Route::delete('addresses/{address}', [AddressController::class, 'destroy']);
        Route::post('addresses/{address}/default', [AddressController::class, 'makeDefault']);

        Route::get('wishlist', [WishlistController::class, 'index']);
        Route::post('wishlist/{product}/toggle', [WishlistController::class, 'toggle']);

        Route::post('products/{slug}/reviews', [ReviewController::class, 'store']);
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);

        Route::get('checkout', [CheckoutController::class, 'show']);
        Route::post('checkout', [CheckoutController::class, 'store']);

        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{orderNumber}', [OrderController::class, 'show']);
        Route::post('orders/{orderNumber}/cancel', [OrderController::class, 'cancel']);
        Route::post('orders/{orderNumber}/return', [OrderController::class, 'requestReturn']);
    });
});
