<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\CartPricing;
use Illuminate\Http\Request;

/**
 * The storefront keeps the cart in the browser and asks for a fresh quote
 * whenever it changes. Prices, stock limits, coupon rules, tax and shipping
 * are always computed here, never trusted from the client.
 */
class CartController extends Controller
{
    public static function itemRules(): array
    {
        return [
            'items' => 'present|array|max:50',
            'items.*.product_id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1|max:999',
            'coupon_code' => 'nullable|string|max:50',
        ];
    }

    public function quote(Request $request)
    {
        $data = $request->validate(self::itemRules());

        $requested = self::toCartMap($data['items']);
        $products = Product::whereIn('id', array_keys($requested))->get()->keyBy('id');

        // Drop what can't be bought any more and clamp quantities to what's
        // available, telling the storefront so it can update the stored cart.
        $cart = [];
        $notices = [];
        foreach ($requested as $productId => $qty) {
            $product = $products[$productId] ?? null;
            if (! $product || ! $product->is_active) {
                $notices[] = ['product_id' => $productId, 'message' => 'An item in your cart is no longer available and was removed.'];
                continue;
            }
            $max = CartPricing::maxQty($product);
            if ($max < 1) {
                $notices[] = ['product_id' => $productId, 'message' => "{$product->name} is out of stock and was removed from your cart."];
                continue;
            }
            if ($qty > $max) {
                $notices[] = ['product_id' => $productId, 'message' => "You can buy up to {$max} of {$product->name}. Your cart has been updated."];
                $qty = $max;
            }
            $cart[$productId] = $qty;
        }

        return response()->json(self::present(CartPricing::quote($cart, $data['coupon_code'] ?? null)) + [
            'notices' => $notices,
        ]);
    }

    /**
     * @param  array<int, array{product_id: int, qty: int}>  $items
     * @return array<int, int>  product id => quantity (duplicates merged)
     */
    public static function toCartMap(array $items): array
    {
        $cart = [];
        foreach ($items as $item) {
            $id = (int) $item['product_id'];
            $cart[$id] = ($cart[$id] ?? 0) + (int) $item['qty'];
        }

        return $cart;
    }

    /** JSON shape of a CartPricing::quote() result. */
    public static function present(array $quote): array
    {
        return [
            'items' => array_map(fn ($line) => [
                'product' => new ProductResource($line['product']),
                'qty' => $line['qty'],
                'subtotal' => round($line['subtotal'], 2),
            ], $quote['items']),
            'item_count' => array_sum(array_column($quote['items'], 'qty')),
            'subtotal' => round($quote['subtotal'], 2),
            'coupon' => $quote['coupon'] ? [
                'code' => $quote['coupon']->code,
                'type' => $quote['coupon']->type,
                'value' => (float) $quote['coupon']->value,
            ] : null,
            'coupon_error' => $quote['couponError'],
            'discount' => round($quote['discount'], 2),
            'tax_rate' => $quote['taxRate'],
            'tax_amount' => $quote['taxAmount'],
            'shipping' => $quote['shipping'],
            'free_shipping_threshold' => $quote['freeShippingThreshold'],
            'free_shipping_remaining' => $quote['freeShippingRemaining'],
            'total' => $quote['total'],
        ];
    }
}
