<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /** Most units of a single product one order line can hold. */
    public const MAX_QTY_PER_ITEM = 10;

    private function cart(): array
    {
        return Session::get('cart', []);
    }

    /**
     * Compute the full cart breakdown (items, subtotal, coupon discount,
     * tax and grand total) so it can be reused by the cart page and by
     * checkout. Re-validates any applied coupon against the current
     * subtotal and drops it silently if it's no longer valid.
     */
    public static function calculate(): array
    {
        $cart = Session::get('cart', []);
        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');
        $subtotal = 0;
        $items = [];

        foreach ($cart as $productId => $qty) {
            if (!isset($products[$productId])) {
                continue;
            }
            $product = $products[$productId];
            $lineSubtotal = $product->finalPrice() * $qty;
            $subtotal += $lineSubtotal;
            $items[] = ['product' => $product, 'qty' => $qty, 'subtotal' => $lineSubtotal];
        }

        $discount = 0;
        $couponCode = Session::get('coupon_code');
        $coupon = null;

        if ($couponCode) {
            $coupon = Coupon::where('code', strtoupper($couponCode))->first();
            if (!$coupon || !$coupon->isValidFor($subtotal)) {
                Session::forget('coupon_code');
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
            'items', 'subtotal', 'coupon', 'discount', 'taxRate', 'taxAmount',
            'shipping', 'freeShippingThreshold', 'freeShippingRemaining', 'total'
        );
    }

    public function index()
    {
        $data = self::calculate();

        return view('cart.index', $data);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string|max:50']);

        $code = strtoupper(trim($request->code));
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return back()->with('error', 'That coupon code is not valid.');
        }

        $subtotal = 0;
        foreach ($this->cart() as $productId => $qty) {
            $product = Product::find($productId);
            if ($product) {
                $subtotal += $product->finalPrice() * $qty;
            }
        }

        if ($error = $coupon->validationError($subtotal)) {
            return back()->with('error', $error);
        }

        Session::put('coupon_code', $coupon->code);

        return back()->with('success', "Coupon \"{$coupon->code}\" applied.");
    }

    public function removeCoupon()
    {
        Session::forget('coupon_code');
        return back()->with('success', 'Coupon removed.');
    }

    /** Highest quantity allowed for a product: capped by stock and the per-item limit. */
    private function maxQty(Product $product): int
    {
        return max(0, min(self::MAX_QTY_PER_ITEM, (int) $product->stock));
    }

    public function add(Request $request, Product $product)
    {
        $max = $this->maxQty($product);
        if ($max < 1) {
            return back()->with('error', $product->name . ' is out of stock.');
        }

        $qty = max(1, (int) $request->input('qty', 1));
        $cart = $this->cart();
        $newQty = ($cart[$product->id] ?? 0) + $qty;
        $cart[$product->id] = min($newQty, $max);
        Session::put('cart', $cart);

        if ($newQty > $max) {
            return back()->with('info', "You can buy up to {$max} of {$product->name}. Your cart has been set to {$max}.");
        }

        return back()->with('success', $product->name . ' added to cart.');
    }

    /**
     * "Buy Now": put exactly the chosen quantity of this product in the cart
     * and jump straight to checkout. Other cart items are left untouched.
     */
    public function buyNow(Request $request, Product $product)
    {
        $max = $this->maxQty($product);
        if ($max < 1) {
            return back()->with('error', $product->name . ' is out of stock.');
        }

        $qty = min(max(1, (int) $request->input('qty', 1)), $max);
        $cart = $this->cart();
        $cart[$product->id] = $qty;
        Session::put('cart', $cart);

        return redirect()->route('checkout.index');
    }

    public function update(Request $request, Product $product)
    {
        $max = max(1, $this->maxQty($product));
        $qty = min(max(1, (int) $request->input('qty', 1)), $max);
        $cart = $this->cart();
        if (isset($cart[$product->id])) {
            $cart[$product->id] = $qty;
            Session::put('cart', $cart);
        }

        return back();
    }

    public function remove(Product $product)
    {
        $cart = $this->cart();
        unset($cart[$product->id]);
        Session::put('cart', $cart);

        return back()->with('success', 'Item removed.');
    }

    public function clear()
    {
        Session::forget('cart');
        return back();
    }

    public static function count(): int
    {
        return array_sum(Session::get('cart', []));
    }
}
