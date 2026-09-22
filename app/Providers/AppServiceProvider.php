<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Behind Render's proxy: always generate https:// asset and route URLs in production.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Share which products the logged-in customer has wishlisted with every
        // view that renders a heart icon (product cards, the product page, and
        // the header's saved-items count), without threading it through every
        // controller that happens to list products.
        View::composer(
            ['shop.partials.card', 'shop.show', 'layouts.app'],
            function ($view) {
                $user = auth()->user();
                $view->with('wishlistIds', $user && ! $user->isAdmin() ? $user->wishlistProductIds() : collect());
            }
        );
    }
}
