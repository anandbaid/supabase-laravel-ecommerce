<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LocalUserResolver;
use App\Services\SupabaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use RuntimeException;
use Throwable;

class AuthController extends Controller
{
    public function __construct(private readonly SupabaseAuthService $supabaseAuth)
    {
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $supabaseUser = $this->supabaseAuth->signIn($credentials['email'], $credentials['password']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['email' => $e->getMessage()])->onlyInput('email');
        } catch (Throwable $e) {
            Log::error('Login failed unexpectedly: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['email' => 'Something went wrong signing you in. Please try again.'])->onlyInput('email');
        }

        try {
            $user = $this->syncLocalUser($supabaseUser, $credentials['email']);

            Auth::login($user, $request->boolean('remember'));
            $request->session()->put('supabase_access_token', $supabaseUser['access_token'] ?? null);
            $request->session()->put('supabase_refresh_token', $supabaseUser['refresh_token'] ?? null);
            $request->session()->regenerate();
        } catch (Throwable $e) {
            Log::error('Local user sync after Supabase login failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['email' => 'Signed in, but we could not load your account. Please try again.'])->onlyInput('email');
        }

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome back!');
        }

        return redirect()->intended(route('home'))->with('success', 'Welcome back!');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        try {
            $supabaseUser = $this->supabaseAuth->signUp($data['email'], $data['password'], [
                'name' => $data['name'],
            ]);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['email' => $e->getMessage()]);
        } catch (Throwable $e) {
            Log::error('Registration failed unexpectedly: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->withErrors(['email' => 'Something went wrong creating your account. Please try again.']);
        }

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => null,
                'role' => 'customer',
                'supabase_uid' => $supabaseUser['id'] ?? null,
            ]);
        } catch (Throwable $e) {
            Log::error('Local user create after Supabase signup failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->withErrors(['email' => 'Your account was created, but we could not finish setup. Please try logging in.']);
        }

        if (! empty($supabaseUser['needs_confirmation'])) {
            return redirect()->route('login')->with('success', 'Account created! Please check your email to confirm your address before logging in.');
        }

        Auth::login($user);
        $request->session()->put('supabase_access_token', $supabaseUser['access_token'] ?? null);
        $request->session()->put('supabase_refresh_token', $supabaseUser['refresh_token'] ?? null);

        return redirect()->route('home')->with('success', 'Welcome to ShopEase!');
    }

    public function logout(Request $request)
    {
        $accessToken = $request->session()->get('supabase_access_token');
        if ($accessToken) {
            $this->supabaseAuth->signOut($accessToken);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Find or create the local mirror User record for a Supabase Auth account,
     * keeping the supabase_uid linked for future lookups.
     */
    private function syncLocalUser(array $supabaseUser, string $email): User
    {
        return app(LocalUserResolver::class)->resolve($supabaseUser['id'] ?? null, $email);
    }
}
