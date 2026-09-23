<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;

/**
 * Prices a cart — a map of product id => quantity — plus an optional coupon
 * code. Shared by the Blade cart (which keeps the cart in the session) and
 * the JSON API (where the Next.js storefront sends the cart with each call),
 * so both always compute identical totals.
 */
class CartPricing
{
    /** Most units of a single product one order line can hold. */
    public const MAX_QTY_PER_ITEM = 10;

    /**
     * @param  array<int|string, int>  $cart  product id => quantity
     * @return array{
     *     items: array<int, array{product: Product, qty: int, subtotal: float}>,
     *     subtotal: float, coupon: ?Coupon, couponError: ?string, discount: float,
     *     taxRate: float, taxAmount: float, shipping: float,
     *     freeShippingThreshold: float, freeShippingRemaining: float, total: float
     * }
     */
    public static function quote(array $cart, ?string $couponCode = null): array
    {
        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');
        $subtotal = 0;
        $items = [];

        foreach ($cart as $productId => $qty) {
            if (! isset($products[$productId])) {
                continue;
            }
            $product = $products[$productId];
            $lineSubtotal = $product->finalPrice() * $qty;
            $subtotal += $lineSubtotal;
            $items[] = ['product' => $product, 'qty' => $qty, 'subtotal' => $lineSubtotal];
        }

        $discount = 0;
        $coupon = null;
        $couponError = null;

        if ($couponCode) {
            $coupon = Coupon::where('code', strtoupper(trim($couponCode)))->first();
            if (! $coupon) {
                $couponError = 'That coupon code is not valid.';
            } elseif ($error = $coupon->validationError($subtotal)) {
                $couponError = $error;
                $coupon = null;
            } else {
                $discount = $coupon->calculateDiscount($subtotal);
            }
        }

        $taxRate = Setting::taxRate();
        $taxable = max($subtotal - $discount, 0);
        $taxAmount = round($taxable * ($taxRate / 100), 2);

        // Flat shipping fee, waived once the (pre-discount) subtotal reaches the threshold.
        $freeShippingThreshold = Setting::freeShippingThreshold();
        $shipping = ($subtotal <= 0 || $subtotal >= $freeShippingThreshold)
            ? 0.0
            : Setting::shippingFee();
        $freeShippingRemaining = ($subtotal > 0 && $subtotal < $freeShippingThreshold)
            ? round($freeShippingThreshold - $subtotal, 2)
            : 0.0;

        $total = round($taxable + $taxAmount + $shipping, 2);

        return compact(
            'items', 'subtotal', 'coupon', 'couponError', 'discount', 'taxRate', 'taxAmount',
            'shipping', 'freeShippingThreshold', 'freeShippingRemaining', 'total'
        );
    }

    /** Highest quantity allowed for a product: capped by stock and the per-item limit. */
    public static function maxQty(Product $product): int
    {
        return max(0, min(self::MAX_QTY_PER_ITEM, (int) $product->stock));
    }
}
