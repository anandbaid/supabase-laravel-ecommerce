<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    /** token => [supabase uid, email] recognised by the fake Supabase Auth. */
    protected array $tokens = [];

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(function ($request) {
            $url = $request->url();

            if (str_ends_with($url, '/auth/v1/user') && $request->method() === 'GET') {
                $token = str_replace('Bearer ', '', $request->header('Authorization')[0] ?? '');
                if (! isset($this->tokens[$token])) {
                    return Http::response(['msg' => 'invalid JWT'], 401);
                }
                [$uid, $email] = $this->tokens[$token];

                return Http::response(['id' => $uid, 'email' => $email, 'user_metadata' => []]);
            }

            if (str_contains($url, '/auth/v1/token?grant_type=password')) {
                $data = $request->data();
                if (($data['password'] ?? '') !== 'secret123') {
                    return Http::response(['error_description' => 'Invalid login credentials'], 400);
                }

                return Http::response([
                    'access_token' => 'access-' . $data['email'],
                    'refresh_token' => 'refresh-' . $data['email'],
                    'expires_in' => 3600,
                    'user' => ['id' => 'uid-' . $data['email'], 'email' => $data['email']],
                ]);
            }

            if (str_contains($url, '/auth/v1/token?grant_type=refresh_token')) {
                if (($request->data()['refresh_token'] ?? '') === 'outage') {
                    return Http::response(['msg' => 'upstream error'], 503);
                }
                if (($request->data()['refresh_token'] ?? '') === 'stale') {
                    return Http::response(['error_description' => 'Invalid Refresh Token'], 400);
                }

                return Http::response(['access_token' => 'new-access', 'refresh_token' => 'new-refresh', 'expires_in' => 3600, 'user' => ['id' => 'x']]);
            }

            if (str_ends_with($url, '/auth/v1/signup')) {
                $data = $request->data();

                return Http::response([
                    'access_token' => 'access-' . $data['email'],
                    'refresh_token' => 'refresh-' . $data['email'],
                    'expires_in' => 3600,
                    'user' => ['id' => 'uid-' . $data['email'], 'email' => $data['email']],
                ]);
            }

            if (str_ends_with($url, '/auth/v1/logout')) {
                return Http::response(null, 204);
            }

            return Http::response(['msg' => 'unexpected ' . $url], 500);
        });
    }

    /** A customer (or admin) with a token the fake Supabase recognises. */
    protected function customer(string $email = 'cust@example.com', string $role = 'customer'): array
    {
        $user = User::create(['name' => 'Cust', 'email' => $email, 'role' => $role, 'supabase_uid' => 'uid-' . $email]);
        $token = 'token-' . $email;
        $this->tokens[$token] = ['uid-' . $email, $email];

        return [$user, ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json']];
    }

    protected function product(array $attrs = []): Product
    {
        $category = Category::firstOrCreate(['name' => 'Gear'], ['is_active' => true]);

        return Product::create($attrs + [
            'category_id' => $category->id, 'name' => 'Tent', 'price' => 40,
            'stock' => 20, 'is_active' => true,
        ]);
    }

    protected function checkoutForm(array $overrides = []): array
    {
        return $overrides + [
            'first_name' => 'Cara', 'last_name' => 'Lee',
            'customer_email' => 'cara@example.com', 'customer_phone' => '555',
            'billing_country' => 'US', 'billing_line1' => '1 Main St',
            'billing_city' => 'Austin', 'billing_state' => 'TX', 'billing_postal_code' => '78701',
            'same_as_billing' => true, 'payment_method' => 'cod',
        ];
    }
}
