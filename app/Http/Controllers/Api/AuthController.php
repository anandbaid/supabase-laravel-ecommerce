<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthenticateSupabaseToken;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\LocalUserResolver;
use App\Services\SupabaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use RuntimeException;
use Throwable;

/**
 * Login / registration for the Next.js storefront. Accounts live in Supabase
 * Auth (exactly as on the Blade site); this returns the Supabase session
 * tokens so the storefront can keep them in httpOnly cookies and send the
 * access token back as a Bearer token.
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly SupabaseAuthService $supabaseAuth,
        private readonly LocalUserResolver $users,
    ) {
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $session = $this->supabaseAuth->signIn($credentials['email'], $credentials['password']);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => ['email' => [$e->getMessage()]]], 422);
        }

        try {
            $user = $this->users->resolve($session['id'] ?? null, $credentials['email']);
        } catch (Throwable $e) {
            Log::error('Local user sync after Supabase login failed: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json(['message' => 'Signed in, but we could not load your account. Please try again.'], 500);
        }

        return response()->json($this->sessionPayload($session, $user));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        try {
            $session = $this->supabaseAuth->signUp($data['email'], $data['password'], ['name' => $data['name']]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => ['email' => [$e->getMessage()]]], 422);
        }

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => null,
                'role' => 'customer',
                'supabase_uid' => $session['id'] ?? null,
            ]);
        } catch (Throwable $e) {
            Log::error('Local user create after Supabase signup failed: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json(['message' => 'Your account was created, but we could not finish setup. Please try logging in.'], 500);
        }

        if (! empty($session['needs_confirmation'])) {
            return response()->json([
                'needs_confirmation' => true,
                'message' => 'Account created! Please check your email to confirm your address before logging in.',
            ], 201);
        }

        return response()->json($this->sessionPayload($session, $user), 201);
    }

    public function refresh(Request $request)
    {
        $data = $request->validate(['refresh_token' => 'required|string']);

        try {
            $session = $this->supabaseAuth->refresh($data['refresh_token']);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }

        return response()->json([
            'access_token' => $session['access_token'],
            'refresh_token' => $session['refresh_token'],
            'expires_in' => $session['expires_in'],
        ]);
    }

    public function logout(Request $request)
    {
        $token = (string) $request->bearerToken();
        $this->supabaseAuth->signOut($token);
        Cache::forget(AuthenticateSupabaseToken::cacheKey($token));

        return response()->json(['message' => 'Logged out.']);
    }

    private function sessionPayload(array $session, User $user): array
    {
        return [
            'access_token' => $session['access_token'],
            'refresh_token' => $session['refresh_token'],
            'expires_in' => $session['expires_in'] ?? null,
            'user' => new UserResource($user),
        ];
    }
}
