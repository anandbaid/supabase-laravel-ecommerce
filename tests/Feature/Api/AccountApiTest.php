<?php

namespace Tests\Feature\Api;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;

class AccountApiTest extends ApiTestCase
{
    private function orderFor($user, array $attrs = []): Order
    {
        $order = Order::create($attrs + [
            'user_id' => $user->id, 'customer_name' => 'C', 'customer_email' => $user->email,
            'shipping_address' => 'x', 'subtotal' => 10, 'total' => 10,
            'status' => 'pending', 'payment_method' => 'cod', 'payment_status' => 'unpaid',
        ]);
        $product = $this->product();
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => $product->name, 'price' => 10, 'quantity' => 1, 'subtotal' => 10]);

        return $order;
    }

    public function test_orders_are_private_to_their_owner(): void
    {
        [$me, $headers] = $this->customer();
        [$other] = $this->customer('other@example.com');
        $mine = $this->orderFor($me);
        $theirs = $this->orderFor($other);

        $this->getJson('/api/v1/orders', $headers)->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.order_number', $mine->order_number)
            ->assertJsonPath('data.0.items_count', 1);

        $this->getJson('/api/v1/orders/' . $theirs->order_number, $headers)->assertNotFound();
        $this->postJson('/api/v1/orders/' . $theirs->order_number . '/cancel', [], $headers)->assertNotFound();
    }

    public function test_cancel_and_return_rules(): void
    {
        [$me, $headers] = $this->customer();
        $pending = $this->orderFor($me);
        $shipped = $this->orderFor($me, ['status' => 'shipped']);
        $delivered = $this->orderFor($me, ['status' => 'delivered', 'delivered_at' => now()->subDays(2)]);
        $old = $this->orderFor($me, ['status' => 'delivered', 'delivered_at' => now()->subDays(10)]);

        $this->postJson("/api/v1/orders/{$pending->order_number}/cancel", ['reason' => 'Changed mind'], $headers)
            ->assertOk()->assertJsonPath('order.status', 'cancelled');
        $this->postJson("/api/v1/orders/{$shipped->order_number}/cancel", [], $headers)->assertStatus(422);

        $this->postJson("/api/v1/orders/{$delivered->order_number}/return", ['reason' => 'Too small'], $headers)
            ->assertOk()->assertJsonPath('order.return_status', 'requested');
        $this->postJson("/api/v1/orders/{$old->order_number}/return", ['reason' => 'Late'], $headers)->assertStatus(422);
    }

    public function test_address_book(): void
    {
        [$me, $headers] = $this->customer();
        [$other] = $this->customer('other@example.com');
        $fields = ['full_name' => 'Me', 'phone' => '1', 'line1' => '1 St', 'city' => 'C', 'state' => 'S', 'postal_code' => '1', 'country' => 'US'];

        $first = $this->postJson('/api/v1/addresses', $fields, $headers)->assertCreated()
            ->assertJsonPath('data.is_default', true)->assertJsonPath('data.label', 'Home')->json('data.id');
        $second = $this->postJson('/api/v1/addresses', $fields + ['label' => 'Work'], $headers)->assertCreated()
            ->assertJsonPath('data.is_default', false)->json('data.id');

        $this->postJson("/api/v1/addresses/{$second}/default", [], $headers)->assertOk()->assertJsonPath('data.is_default', true);
        $this->assertFalse(Address::find($first)->is_default);

        $this->putJson("/api/v1/addresses/{$first}", ['city' => 'New'] + $fields, $headers)->assertOk()->assertJsonPath('data.city', 'New');

        $theirs = Address::create(['user_id' => $other->id] + $fields);
        $this->getJson("/api/v1/addresses/{$theirs->id}", $headers)->assertNotFound();
        $this->deleteJson("/api/v1/addresses/{$theirs->id}", [], $headers)->assertNotFound();

        $this->deleteJson("/api/v1/addresses/{$first}", [], $headers)->assertOk();
        $this->getJson('/api/v1/addresses', $headers)->assertJsonCount(1, 'data');
    }

    public function test_wishlist_toggle(): void
    {
        [, $headers] = $this->customer();
        $tent = $this->product();

        $this->postJson("/api/v1/wishlist/{$tent->id}/toggle", [], $headers)->assertJsonPath('wishlisted', true)->assertJsonPath('count', 1);
        $this->getJson('/api/v1/me', $headers)->assertJsonPath('wishlist_ids', [$tent->id]);
        $this->getJson('/api/v1/wishlist', $headers)->assertJsonPath('data.0.id', $tent->id);
        $this->postJson("/api/v1/wishlist/{$tent->id}/toggle", [], $headers)->assertJsonPath('wishlisted', false);

        [, $admin] = $this->customer('boss@example.com', 'admin');
        $this->postJson("/api/v1/wishlist/{$tent->id}/toggle", [], $admin)->assertStatus(422);
    }

    public function test_reviews(): void
    {
        [$me, $headers] = $this->customer();
        [, $otherHeaders] = $this->customer('other@example.com');
        $tent = $this->product();
        $this->orderFor($me); // creates an order for a different product
        OrderItem::create(['order_id' => Order::first()->id, 'product_id' => $tent->id, 'product_name' => 'Tent', 'price' => 1, 'quantity' => 1, 'subtotal' => 1]);

        $this->postJson("/api/v1/products/{$tent->slug}/reviews", ['rating' => 5, 'body' => 'Too short'], $headers)
            ->assertStatus(422)->assertJsonValidationErrors('body');

        $this->postJson("/api/v1/products/{$tent->slug}/reviews", ['rating' => 5, 'body' => 'Great tent, kept us dry.'], $headers)
            ->assertCreated()->assertJsonPath('review.is_verified_purchase', true)->assertJsonPath('review.user.name', 'Cust');
        $this->postJson("/api/v1/products/{$tent->slug}/reviews", ['rating' => 4, 'body' => 'Still good after a month.'], $headers)
            ->assertOk()->assertJsonPath('review.rating', 4);

        $this->getJson("/api/v1/products/{$tent->slug}", $headers)
            ->assertJsonPath('can_review', true)
            ->assertJsonPath('my_review.rating', 4)
            ->assertJsonPath('review_summary.count', 1);
        $this->getJson("/api/v1/products/{$tent->slug}/reviews")->assertJsonPath('data.0.user.name', 'Cust');

        $review = Review::sole();
        $this->deleteJson("/api/v1/reviews/{$review->id}", [], $otherHeaders)->assertForbidden();
        $this->deleteJson("/api/v1/reviews/{$review->id}", [], $headers)->assertOk();
    }

    public function test_dashboard_and_profile(): void
    {
        [$me, $headers] = $this->customer();
        $this->orderFor($me);

        $this->getJson('/api/v1/account/dashboard', $headers)->assertOk()
            ->assertJsonPath('orders_count', 1)
            ->assertJsonPath('loyalty_points', 50)
            ->assertJsonCount(1, 'recent_orders');

        $this->patchJson('/api/v1/me', ['name' => 'Renamed', 'email' => $me->email, 'phone' => '999'], $headers)
            ->assertOk()->assertJsonPath('user.name', 'Renamed');
    }

    public function test_newsletter_and_contact(): void
    {
        $this->postJson('/api/v1/newsletter', ['email' => 'n@example.com'])->assertOk();
        $this->postJson('/api/v1/newsletter', ['email' => 'n@example.com'])->assertOk();
        $this->assertDatabaseCount('subscribers', 1);

        $this->postJson('/api/v1/contact', ['name' => 'Ann', 'email' => 'a@example.com', 'message' => 'Hi'])
            ->assertOk()->assertJsonPath('message', "Thanks Ann! We've received your message and will get back to you soon.");
    }
}
