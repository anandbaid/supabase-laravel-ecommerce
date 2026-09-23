<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Coupon;

class CatalogAndCartTest extends ApiTestCase
{
    public function test_home_and_product_listing(): void
    {
        $this->product(['name' => 'Featured Tent', 'is_featured' => true]);
        $this->product(['name' => 'Hidden', 'is_active' => false]);
        $this->product(['name' => 'Cheap Lamp', 'price' => 5, 'discount_price' => 4]);

        $this->getJson('/api/v1/home')->assertOk()
            ->assertJsonCount(1, 'featured')
            ->assertJsonCount(2, 'latest');

        $this->getJson('/api/v1/products?sort=price_low')->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.name', 'Cheap Lamp')
            ->assertJsonPath('data.0.final_price', 4)
            ->assertJsonPath('data.0.discount_percent', 20);

        $this->getJson('/api/v1/products?deals=1')->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/products?search=lamp')->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/products/suggest?q=te')->assertJsonCount(1, 'data');
    }

    public function test_parent_category_includes_subcategories(): void
    {
        $parent = Category::create(['name' => 'Outdoors', 'is_active' => true]);
        $child = Category::create(['name' => 'Camping', 'parent_id' => $parent->id, 'is_active' => true]);
        $this->product(['name' => 'A', 'category_id' => $parent->id]);
        $this->product(['name' => 'B', 'category_id' => $child->id]);

        $this->getJson('/api/v1/products?category=' . $parent->slug)->assertJsonPath('meta.total', 2);
        $this->getJson('/api/v1/products?category=' . $child->slug)->assertJsonPath('meta.total', 1);
        $outdoors = collect($this->getJson('/api/v1/categories')->json('data'))->firstWhere('name', 'Outdoors');
        $this->assertSame('Camping', $outdoors['children'][0]['name']);
        // Subcategories only appear nested, never as top-level entries.
        $this->assertNull(collect($this->getJson('/api/v1/categories')->json('data'))->firstWhere('name', 'Camping'));
    }

    public function test_product_detail_and_inactive_product_is_404(): void
    {
        $product = $this->product();
        $hidden = $this->product(['name' => 'Hidden', 'is_active' => false]);

        $this->getJson('/api/v1/products/' . $product->slug)->assertOk()
            ->assertJsonPath('product.sku', $product->sku)
            ->assertJsonPath('can_review', false)
            ->assertJsonPath('review_summary.count', 0);

        $this->getJson('/api/v1/products/' . $hidden->slug)->assertNotFound();
    }

    public function test_quote_prices_clamps_and_drops_items(): void
    {
        $tent = $this->product(['price' => 40, 'discount_price' => 30, 'stock' => 3]);
        $gone = $this->product(['name' => 'Gone', 'stock' => 0]);

        $this->postJson('/api/v1/cart/quote', ['items' => [
            ['product_id' => $tent->id, 'qty' => 5],
            ['product_id' => $gone->id, 'qty' => 1],
            ['product_id' => 99999, 'qty' => 1],
        ]])->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.qty', 3)
            ->assertJsonPath('subtotal', 90)
            ->assertJsonPath('shipping', 0)
            ->assertJsonPath('total', 90)
            ->assertJsonCount(3, 'notices');
    }

    public function test_quote_applies_and_rejects_coupons(): void
    {
        $tent = $this->product(['price' => 20]);
        Coupon::create(['code' => 'HALF', 'type' => 'percent', 'value' => 50, 'min_order_amount' => 30, 'is_active' => true]);

        // Below the coupon minimum: error, no discount; below $50 so $2 shipping applies.
        $this->postJson('/api/v1/cart/quote', ['items' => [['product_id' => $tent->id, 'qty' => 1]], 'coupon_code' => 'half'])
            ->assertJsonPath('coupon', null)
            ->assertJsonPath('discount', 0)
            ->assertJsonPath('shipping', 2)
            ->assertJsonPath('free_shipping_remaining', 30)
            ->assertJsonPath('coupon_error', 'Add items worth at least $30.00 to use this coupon.');

        $this->postJson('/api/v1/cart/quote', ['items' => [['product_id' => $tent->id, 'qty' => 2]], 'coupon_code' => 'half'])
            ->assertJsonPath('coupon.code', 'HALF')
            ->assertJsonPath('discount', 20)
            ->assertJsonPath('total', 22);

        $this->postJson('/api/v1/cart/quote', ['items' => [], 'coupon_code' => 'NOPE'])
            ->assertJsonPath('coupon_error', 'That coupon code is not valid.');
    }
}
