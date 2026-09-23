<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class AuthApiTest extends ApiTestCase
{
    public function test_login_returns_tokens_and_links_local_user(): void
    {
        User::create(['name' => 'Old', 'email' => 'old@example.com', 'role' => 'customer']);

        $this->postJson('/api/v1/auth/login', ['email' => 'old@example.com', 'password' => 'secret123'])
            ->assertOk()
            ->assertJsonPath('access_token', 'access-old@example.com')
            ->assertJsonPath('refresh_token', 'refresh-old@example.com')
            ->assertJsonPath('expires_in', 3600)
            ->assertJsonPath('user.name', 'Old')
            ->assertJsonPath('user.is_admin', false);

        $this->assertSame('uid-old@example.com', User::where('email', 'old@example.com')->value('supabase_uid'));
    }

    public function test_bad_password_is_a_validation_error(): void
    {
        $this->postJson('/api/v1/auth/login', ['email' => 'a@example.com', 'password' => 'nope'])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'Invalid login credentials');
    }

    public function test_register_creates_customer(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'New Person', 'email' => 'new@example.com',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertCreated()->assertJsonPath('user.name', 'New Person');

        $this->assertDatabaseHas('users', ['email' => 'new@example.com', 'role' => 'customer', 'supabase_uid' => 'uid-new@example.com']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Again', 'email' => 'new@example.com',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_refresh(): void
    {
        $this->postJson('/api/v1/auth/refresh', ['refresh_token' => 'good'])
            ->assertOk()->assertJsonPath('access_token', 'new-access');
        $this->postJson('/api/v1/auth/refresh', ['refresh_token' => 'stale'])->assertUnauthorized();
    }

    public function test_refresh_during_auth_outage_is_not_a_logout(): void
    {
        // Supabase 5xx: the token may be fine, so answer 503 (the storefront keeps the session).
        $this->postJson('/api/v1/auth/refresh', ['refresh_token' => 'outage'])->assertStatus(503);
    }

    public function test_protected_routes_need_a_valid_token(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
        $this->getJson('/api/v1/me', ['Authorization' => 'Bearer forged'])->assertUnauthorized();

        [$user, $headers] = $this->customer();
        $this->getJson('/api/v1/me', $headers)->assertOk()->assertJsonPath('user.email', $user->email);
    }

    public function test_token_lookup_is_cached_and_logout_forgets_it(): void
    {
        [, $headers] = $this->customer();

        $this->getJson('/api/v1/me', $headers)->assertOk();
        $this->getJson('/api/v1/me', $headers)->assertOk();
        Http::assertSentCount(1);

        $this->postJson('/api/v1/auth/logout', [], $headers)->assertOk();
        unset($this->tokens['token-cust@example.com']); // Supabase revoked it
        $this->getJson('/api/v1/me', $headers)->assertUnauthorized();
    }

    public function test_unknown_supabase_user_gets_a_local_customer_row(): void
    {
        $this->tokens['fresh'] = ['uid-fresh', 'fresh@example.com'];

        $this->getJson('/api/v1/me', ['Authorization' => 'Bearer fresh'])->assertOk()->assertJsonPath('user.name', 'fresh');
        $this->assertDatabaseHas('users', ['email' => 'fresh@example.com', 'supabase_uid' => 'uid-fresh']);
    }
}
