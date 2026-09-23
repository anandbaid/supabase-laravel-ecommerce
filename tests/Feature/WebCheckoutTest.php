<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the existing Blade checkout while its internals are shared with the
 * JSON API: a cash-on-delivery order must still be priced, saved, and stock
 * decremented exactly as before.
 */
class WebCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_cod_checkout_creates_order_and_decrements_stock(): void
    {
        $category = Category::create(['name' => 'Gear', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'Tent', 'price' => 40,
            'discount_price' => 30, 'stock' => 5, 'is_active' => true,
        ]);
        Coupon::create(['code' => 'TEN', 'type' => 'fixed', 'value' => 10, 'min_order_amount' => 0, 'is_active' => true]);
        $user = User::create(['name' => 'Cara', 'email' => 'cara@example.com', 'role' => 'customer']);

        $this->withoutVite();

        $response = $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 2], 'coupon_code' => 'TEN'])
            ->post('/checkout', [
                'first_name' => 'Cara', 'last_name' => 'Lee',
                'customer_email' => 'cara@example.com', 'customer_phone' => '555',
                'billing_country' => 'US', 'billing_line1' => '1 Main St',
                'billing_city' => 'Austin', 'billing_state' => 'TX', 'billing_postal_code' => '78701',
                'same_as_billing' => '1', 'payment_method' => 'cod',
            ]);

        $order = Order::with('items')->sole();
        $response->assertRedirect(route('checkout.success', $order->order_number));
        $this->get(route('checkout.success', $order->order_number))->assertOk()->assertSee($order->order_number);

        $this->assertSame('60.00', number_format((float) $order->subtotal, 2));
        $this->assertSame('10.00', number_format((float) $order->discount_amount, 2));
        // Subtotal 60 is above the default $50 free-shipping threshold, tax defaults to 0%.
        $this->assertSame('50.00', number_format((float) $order->total, 2));
        $this->assertSame('TEN', $order->coupon_code);
        $this->assertCount(1, $order->items);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(1, Coupon::first()->used_count);
    }

    public function test_cart_and_checkout_pages_still_render_with_an_expired_coupon(): void
    {
        $this->withoutVite();
        $category = Category::create(['name' => 'Gear', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Lamp', 'price' => 10, 'stock' => 5, 'is_active' => true]);
        Coupon::create(['code' => 'OLD', 'type' => 'fixed', 'value' => 5, 'min_order_amount' => 0, 'is_active' => true, 'expires_at' => now()->subDay()]);
        $user = User::create(['name' => 'Cara', 'email' => 'cara@example.com', 'role' => 'customer']);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1], 'coupon_code' => 'OLD'])
            ->get('/cart')
            ->assertOk()
            ->assertSee('Lamp')
            ->assertSessionMissing('coupon_code');

        $this->actingAs($user)->withSession(['cart' => [$product->id => 1]])->get('/checkout')->assertOk();
    }
}
