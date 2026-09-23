<?php

namespace Tests\Feature\Api;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;

class CheckoutApiTest extends ApiTestCase
{
    public function test_cod_checkout_places_order(): void
    {
        [$user, $headers] = $this->customer();
        $tent = $this->product(['price' => 40, 'discount_price' => 30, 'stock' => 5]);
        Coupon::create(['code' => 'TEN', 'type' => 'fixed', 'value' => 10, 'min_order_amount' => 0, 'is_active' => true]);

        $response = $this->postJson('/api/v1/checkout', $this->checkoutForm([
            'items' => [['product_id' => $tent->id, 'qty' => 2]],
            'coupon_code' => 'ten',
        ]), $headers)->assertCreated()->assertJsonPath('checkout_url', null);

        $order = Order::with('items')->sole();
        $this->assertSame($order->order_number, $response->json('order_number'));
        $this->assertSame($user->id, $order->user_id);
        $this->assertEquals(50.0, (float) $order->total);
        $this->assertSame('TEN', $order->coupon_code);
        $this->assertSame(3, $tent->fresh()->stock);
        $this->assertSame(1, Address::where('user_id', $user->id)->where('is_default', true)->count());

        $this->getJson('/api/v1/orders/' . $order->order_number, $headers)->assertOk()
            ->assertJsonPath('data.items.0.product_name', 'Tent')
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.can_cancel', true);
    }

    public function test_quantity_above_stock_is_rejected_not_clamped(): void
    {
        [, $headers] = $this->customer();
        $tent = $this->product(['stock' => 1]);

        $this->postJson('/api/v1/checkout', $this->checkoutForm(['items' => [['product_id' => $tent->id, 'qty' => 2]]]), $headers)
            ->assertStatus(409)
            ->assertJsonPath('message', 'Only 1 of Tent can be ordered. Please review your cart.');
        $this->assertSame(0, Order::count());
    }

    public function test_invalid_coupon_blocks_checkout(): void
    {
        [, $headers] = $this->customer();
        $tent = $this->product();

        $this->postJson('/api/v1/checkout', $this->checkoutForm([
            'items' => [['product_id' => $tent->id, 'qty' => 1]], 'coupon_code' => 'NOPE',
        ]), $headers)->assertStatus(422)->assertJsonValidationErrors('coupon_code');
    }

    public function test_guests_admins_and_empty_carts(): void
    {
        $tent = $this->product();
        $form = $this->checkoutForm(['items' => [['product_id' => $tent->id, 'qty' => 1]]]);

        $this->postJson('/api/v1/checkout', $form)->assertUnauthorized();

        [, $admin] = $this->customer('boss@example.com', 'admin');
        $this->postJson('/api/v1/checkout', $form, $admin)->assertForbidden();

        [, $headers] = $this->customer();
        $this->postJson('/api/v1/checkout', $this->checkoutForm(['items' => []]), $headers)->assertStatus(422);
        $this->postJson('/api/v1/checkout', ['items' => [['product_id' => $tent->id, 'qty' => 1]]], $headers)
            ->assertStatus(422)->assertJsonValidationErrors(['first_name', 'billing_line1', 'payment_method']);
    }

    public function test_saved_address_must_belong_to_the_customer(): void
    {
        [, $headers] = $this->customer();
        [$other] = $this->customer('other@example.com');
        $theirs = Address::create([
            'user_id' => $other->id, 'full_name' => 'O', 'phone' => '1', 'line1' => 'x',
            'city' => 'c', 'state' => 's', 'postal_code' => 'p', 'country' => 'US',
        ]);
        $tent = $this->product();

        $this->postJson('/api/v1/checkout', $this->checkoutForm([
            'items' => [['product_id' => $tent->id, 'qty' => 1]],
            'same_as_billing' => false, 'address_id' => $theirs->id,
        ]), $headers)->assertStatus(422)->assertJsonPath('message', 'That address could not be used. Please choose another.');
    }

    public function test_card_payment_failure_reports_error(): void
    {
        // No Stripe key in tests, so creating the Checkout Session fails.
        [, $headers] = $this->customer();
        $tent = $this->product();

        $this->postJson('/api/v1/checkout', $this->checkoutForm([
            'items' => [['product_id' => $tent->id, 'qty' => 1]], 'payment_method' => 'card',
        ]), $headers)->assertStatus(502)->assertJsonStructure(['message', 'order_number']);
    }
}
