<?php

namespace App\Services;

use App\Models\User;

/**
 * Finds (or creates) the local `users` row that mirrors a Supabase Auth
 * account, keeping `supabase_uid` linked. Orders, addresses, reviews and the
 * wishlist all hang off the local user id.
 */
class LocalUserResolver
{
    public function resolve(?string $supabaseUid, string $email, ?string $name = null): User
    {
        $user = null;

        if (! empty($supabaseUid)) {
            $user = User::where('supabase_uid', $supabaseUid)->first();
        }

        if (! $user) {
            $user = User::where('email', $email)->first();
        }

        if (! $user) {
            return User::create([
                'name' => $name ?: explode('@', $email)[0],
                'email' => $email,
                'password' => null,
                'role' => 'customer',
                'supabase_uid' => $supabaseUid,
            ]);
        }

        if (empty($user->supabase_uid) && ! empty($supabaseUid)) {
            $user->update(['supabase_uid' => $supabaseUid]);
        }

        return $user;
    }
}
