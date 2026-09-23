<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserResource;
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

    /** The signed-in user plus the counts the header needs. */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => new UserResource($user),
            'wishlist_ids' => $user->isAdmin() ? [] : $user->wishlistProductIds()->values(),
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $ordersCount = $user->orders()->count();

        return response()->json([
            'user' => new UserResource($user),
            'orders_count' => $ordersCount,
            'wishlist_count' => $user->wishlists()->count(),
            // Simple placeholder scheme, same as the Blade dashboard.
            'loyalty_points' => $ordersCount * 50,
            'recent_orders' => OrderResource::collection(
                $user->orders()->with('items.product')->latest()->take(3)->get()
            ),
        ]);
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
            try {
                $this->supabaseAuth->updateSelf((string) $request->bearerToken(), [
                    'email' => $emailChanged ? $data['email'] : null,
                    'password' => $passwordChanged ? $data['password'] : null,
                ]);
            } catch (RuntimeException $e) {
                return response()->json(['message' => $e->getMessage(), 'errors' => ['email' => [$e->getMessage()]]], 422);
            } catch (Throwable $e) {
                return response()->json(['message' => 'Something went wrong updating your account. Please try again.'], 500);
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

        return response()->json(['message' => $message, 'user' => new UserResource($user->fresh())]);
    }
}
