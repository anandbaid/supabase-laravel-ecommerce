<?php

namespace App\Http\Controllers;

use App\Services\SupabaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use RuntimeException;
use Throwable;

class AccountController extends Controller
{
    public function __construct(private readonly SupabaseAuthService $supabaseAuth)
    {
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();

        $ordersCount = $user->orders()->count();
        $wishlistCount = $user->wishlists()->count();
        // Simple placeholder scheme — swap in real loyalty logic whenever you have it.
        $loyaltyPoints = $ordersCount * 50;

        $recentOrders = $user->orders()->with('items.product')->latest()->take(3)->get();

        return view('account.dashboard', compact('user', 'ordersCount', 'wishlistCount', 'loyaltyPoints', 'recentOrders'));
    }

    public function edit(Request $request)
    {
        return view('account.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ]);

        $emailChanged = $data['email'] !== $user->email;
        $passwordChanged = ! empty($data['password']);

        if ($emailChanged || $passwordChanged) {
            $accessToken = $request->session()->get('supabase_access_token');

            if (! $accessToken) {
                return back()->withErrors(['email' => 'Your session has expired. Please log out and back in to change your email or password.']);
            }

            try {
                $this->supabaseAuth->updateSelf($accessToken, [
                    'email' => $emailChanged ? $data['email'] : null,
                    'password' => $passwordChanged ? $data['password'] : null,
                ]);
            } catch (RuntimeException $e) {
                return back()->withInput()->withErrors(['email' => $e->getMessage()]);
            } catch (Throwable $e) {
                return back()->withInput()->withErrors(['email' => 'Something went wrong updating your account. Please try again.']);
            }
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        $message = 'Account details updated.';
        if ($emailChanged) {
            $message .= ' Check your inbox to confirm your new email address.';
        }

        return back()->with('success', $message);
    }
}
