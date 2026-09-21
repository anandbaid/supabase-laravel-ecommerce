<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
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
        $total = round($taxable + $taxAmount, 2);

        return compact('items', 'subtotal', 'coupon', 'discount', 'taxRate', 'taxAmount', 'total');
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

    public function add(Request $request, Product $product)
    {
        $qty = max(1, (int) $request->input('qty', 1));
        $cart = $this->cart();
        $cart[$product->id] = ($cart[$product->id] ?? 0) + $qty;
        Session::put('cart', $cart);

        return back()->with('success', $product->name . ' added to cart.');
    }

    public function update(Request $request, Product $product)
    {
        $qty = max(1, (int) $request->input('qty', 1));
        $cart = $this->cart();
        if (isset($cart[$product->id])) {
            $cart[$product->id] = $qty;
            Session::put('cart', $cart);
        }

        return back()->with('success', 'Cart updated.');
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
