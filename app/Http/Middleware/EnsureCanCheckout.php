<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the checkout flow only:
 *  - Guests (not logged in) are sent to login, then bounced back to checkout.
 *  - Logged-in admins are blocked from checking out as a customer.
 * Browsing the shop and adding items to the cart stay open to everyone;
 * this middleware is applied to the /checkout routes only.
 */
class EnsureCanCheckout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()
                ->guest(route('login'))
                ->with('error', 'Please log in to checkout.');
        }

        if ($request->user()->isAdmin()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Admin accounts cannot place orders. Please use a customer account to checkout.');
        }

        return $next($request);
    }
}
