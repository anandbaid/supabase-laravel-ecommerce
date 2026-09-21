<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Thin wrapper around Supabase's GoTrue Auth REST API.
 * No Supabase PHP SDK required — plain HTTP calls only.
 */
class SupabaseAuthService
{
    private string $baseUrl;
    private string $anonKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.supabase.url'), '/') . '/auth/v1';
        $this->anonKey = (string) config('services.supabase.anon_key');

        if (! $this->baseUrl || ! $this->anonKey) {
            throw new RuntimeException('Supabase URL / anon key are not configured.');
        }
    }

    /**
     * Sign up a new user in Supabase Auth.
     * Returns ['id' => supabase_uid, 'email' => ..., 'access_token' => ..., 'refresh_token' => ...]
     *
     * @throws RuntimeException
     */
    public function signUp(string $email, string $password, array $metadata = []): array
    {
        try {
            $response = Http::withHeaders(['apikey' => $this->anonKey])
                ->post("{$this->baseUrl}/signup", [
                    'email' => $email,
                    'password' => $password,
                    'data' => $metadata,
                ]);

            $response->throw();
            $body = $response->json();

            return [
                'id' => $body['user']['id'] ?? $body['id'] ?? null,
                'email' => $body['user']['email'] ?? $body['email'] ?? $email,
                'access_token' => $body['access_token'] ?? null,
                'refresh_token' => $body['refresh_token'] ?? null,
                // If email confirmation is required, Supabase returns the user
                // but no session/tokens yet.
                'needs_confirmation' => empty($body['access_token']),
            ];
        } catch (RequestException $e) {
            $message = $e->response->json('msg') ?? $e->response->json('error_description') ?? $e->response->json('message') ?? 'Sign up failed.';
            throw new RuntimeException($message, previous: $e);
        } catch (Throwable $e) {
            Log::error('Supabase signUp failed: ' . $e->getMessage(), ['exception' => $e]);
            throw new RuntimeException('Could not reach the authentication service.', previous: $e);
        }
    }

    /**
     * Create (or confirm) a user directly in Supabase Auth using the
     * service-role key. Used to provision the admin account, since normal
     * signUp() requires email confirmation flows that don't apply to admins.
     *
     * @throws RuntimeException
     */
    public function adminCreateUser(string $email, string $password, array $metadata = []): array
    {
        $serviceKey = (string) config('services.supabase.service_key');
        if (! $serviceKey) {
            throw new RuntimeException('SUPABASE_SERVICE_KEY is not configured.');
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $serviceKey,
                'Authorization' => "Bearer {$serviceKey}",
            ])->post("{$this->baseUrl}/admin/users", [
                'email' => $email,
                'password' => $password,
                'email_confirm' => true,
                'user_metadata' => $metadata,
            ]);

            $response->throw();
            $body = $response->json();

            return [
                'id' => $body['id'] ?? null,
                'email' => $body['email'] ?? $email,
            ];
        } catch (RequestException $e) {
            $message = $e->response->json('msg') ?? $e->response->json('error_description') ?? 'Could not create the admin user in Supabase.';
            throw new RuntimeException($message, previous: $e);
        } catch (Throwable $e) {
            Log::error('Supabase adminCreateUser failed: ' . $e->getMessage(), ['exception' => $e]);
            throw new RuntimeException('Could not reach the authentication service.', previous: $e);
        }
    }

    /**
     * Update the currently-authenticated user's own email/password in
     * Supabase Auth, using their own access token (not the service key).
     * Used by the "My Account" self-service page.
     *
     * @throws RuntimeException
     */
    public function updateSelf(string $accessToken, array $attributes): array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->anonKey,
                'Authorization' => "Bearer {$accessToken}",
            ])->put("{$this->baseUrl}/user", array_filter($attributes, fn ($v) => $v !== null && $v !== ''));

            $response->throw();

            return $response->json();
        } catch (RequestException $e) {
            $message = $e->response->json('msg') ?? $e->response->json('error_description') ?? 'Could not update your account.';
            throw new RuntimeException($message, previous: $e);
        } catch (Throwable $e) {
            Log::error('Supabase updateSelf failed: ' . $e->getMessage(), ['exception' => $e]);
            throw new RuntimeException('Could not reach the authentication service.', previous: $e);
        }
    }

    /**
     * Update another user's email/password/metadata in Supabase Auth using
     * the service-role key. Used by the admin "Edit customer" screen.
     *
     * @throws RuntimeException
     */
    public function adminUpdateUser(string $supabaseUid, array $attributes): array
    {
        $serviceKey = (string) config('services.supabase.service_key');
        if (! $serviceKey) {
            throw new RuntimeException('SUPABASE_SERVICE_KEY is not configured.');
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $serviceKey,
                'Authorization' => "Bearer {$serviceKey}",
            ])->put("{$this->baseUrl}/admin/users/{$supabaseUid}", array_filter($attributes, fn ($v) => $v !== null && $v !== ''));

            $response->throw();

            return $response->json();
        } catch (RequestException $e) {
            $message = $e->response->json('msg') ?? $e->response->json('error_description') ?? 'Could not update the user in Supabase.';
            throw new RuntimeException($message, previous: $e);
        } catch (Throwable $e) {
            Log::error('Supabase adminUpdateUser failed: ' . $e->getMessage(), ['exception' => $e]);
            throw new RuntimeException('Could not reach the authentication service.', previous: $e);
        }
    }

    /**
     * Delete a user from Supabase Auth using the service-role key.
     * Best-effort: logs but does not throw, so a missing/already-deleted
     * Supabase user never blocks removing the local record.
     */
    public function adminDeleteUser(string $supabaseUid): void
    {
        $serviceKey = (string) config('services.supabase.service_key');
        if (! $serviceKey) {
            return;
        }

        try {
            Http::withHeaders([
                'apikey' => $serviceKey,
                'Authorization' => "Bearer {$serviceKey}",
            ])->delete("{$this->baseUrl}/admin/users/{$supabaseUid}");
        } catch (Throwable $e) {
            Log::error('Supabase adminDeleteUser failed: ' . $e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * Sign in an existing user with email/password.
     *
     * @throws RuntimeException
     */
    public function signIn(string $email, string $password): array
    {
        try {
            $response = Http::withHeaders(['apikey' => $this->anonKey])
                ->post("{$this->baseUrl}/token?grant_type=password", [
                    'email' => $email,
                    'password' => $password,
                ]);

            $response->throw();
            $body = $response->json();

            return [
                'id' => $body['user']['id'] ?? null,
                'email' => $body['user']['email'] ?? $email,
                'access_token' => $body['access_token'] ?? null,
                'refresh_token' => $body['refresh_token'] ?? null,
            ];
        } catch (RequestException $e) {
            $message = $e->response->json('error_description') ?? $e->response->json('msg') ?? 'Invalid email or password.';
            throw new RuntimeException($message, previous: $e);
        } catch (Throwable $e) {
            Log::error('Supabase signIn failed: ' . $e->getMessage(), ['exception' => $e]);
            throw new RuntimeException('Could not reach the authentication service.', previous: $e);
        }
    }

    /**
     * Verify an access token and return the Supabase user it belongs to, or null.
     */
    public function getUser(string $accessToken): ?array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->anonKey,
                'Authorization' => "Bearer {$accessToken}",
            ])->get("{$this->baseUrl}/user");

            if ($response->failed()) {
                return null;
            }

            return $response->json();
        } catch (Throwable $e) {
            Log::error('Supabase getUser failed: ' . $e->getMessage(), ['exception' => $e]);
            return null;
        }
    }

    /**
     * Revoke the given access token (best-effort; non-fatal on failure).
     */
    public function signOut(string $accessToken): void
    {
        try {
            Http::withHeaders([
                'apikey' => $this->anonKey,
                'Authorization' => "Bearer {$accessToken}",
            ])->post("{$this->baseUrl}/logout");
        } catch (Throwable $e) {
            Log::error('Supabase signOut failed: ' . $e->getMessage(), ['exception' => $e]);
        }
    }
}
