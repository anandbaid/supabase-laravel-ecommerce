<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\LocalUserResolver;
use App\Services\SupabaseAuthService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates JSON API requests from the Next.js storefront.
 *
 * The storefront sends the customer's Supabase access token as a Bearer
 * token. It is verified with Supabase Auth (cached briefly so a page that
 * makes several API calls only costs one round trip) and mapped to the
 * local user row.
 *
 * Usage: `supabase.auth` (a valid token is required) or
 *        `supabase.auth:optional` (the user is attached when a valid token
 *        is present, but guests are let through).
 */
class AuthenticateSupabaseToken
{
    /** How long a verified token -> user lookup is trusted, in seconds. */
    private const CACHE_SECONDS = 60;

    public function __construct(
        private readonly SupabaseAuthService $supabaseAuth,
        private readonly LocalUserResolver $users,
    ) {
    }

    public function handle(Request $request, Closure $next, ?string $mode = null): Response
    {
        $token = $request->bearerToken();
        $user = $token ? $this->userForToken($token) : null;

        if (! $user) {
            if ($mode === 'optional') {
                return $next($request);
            }

            return response()->json(['message' => 'Please log in to continue.'], 401);
        }

        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }

    public static function cacheKey(string $token): string
    {
        return 'supabase-token:' . hash('sha256', $token);
    }

    private function userForToken(string $token): ?User
    {
        $userId = Cache::remember(self::cacheKey($token), self::CACHE_SECONDS, function () use ($token) {
            $supabaseUser = $this->supabaseAuth->getUser($token);

            if (! $supabaseUser || empty($supabaseUser['email'])) {
                return null;
            }

            return $this->users->resolve(
                $supabaseUser['id'] ?? null,
                $supabaseUser['email'],
                $supabaseUser['user_metadata']['name'] ?? null
            )->id;
        });

        return $userId ? User::find($userId) : null;
    }
}
