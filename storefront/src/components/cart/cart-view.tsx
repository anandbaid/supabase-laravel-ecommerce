"use client";

import Link from "next/link";
import {
  ArrowLeft,
  Check,
  Headphones,
  Lock,
  Minus,
  PartyPopper,
  Plus,
  RefreshCw,
  ShieldCheck,
  ShoppingBag,
  ShoppingCart,
  Ticket,
  Trash2,
  Truck,
} from "lucide-react";
import { useCart } from "@/components/providers/cart";
import { useSession } from "@/components/providers/session";
import { Alert } from "@/components/ui/form";
import { money, plural } from "@/lib/format";
import { CouponForm } from "./coupon-form";
import { SummaryRows } from "./order-summary-rows";
import { useCartQuote } from "./use-quote";

export function CartView() {
  const cart = useCart();
  const { user } = useSession();
  const { quote, error, couponError, loading } = useCartQuote();
  const itemCount = cart.count;
  const empty = cart.hydrated && itemCount === 0;

  return (
    <div className="mx-auto max-w-7xl px-4 py-8">
      <div className="mb-6 flex items-center gap-4">
        <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
          <ShoppingCart className="h-6 w-6" />
        </div>
        <div>
          <h1 className="text-2xl font-bold text-slate-900">Your Cart</h1>
          <p className="text-sm text-gray-500">
            {cart.hydrated ? `${itemCount} ${plural("item", itemCount)} in your cart` : "Loading your cart…"}
          </p>
        </div>
      </div>

      {error && <div className="mb-4"><Alert tone="error">{error}</Alert></div>}

      {empty ? (
        <div className="rounded-2xl border border-gray-100 bg-white p-12 text-center shadow-sm">
          <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-500">
            <ShoppingBag className="h-7 w-7" />
          </div>
          <h2 className="mb-1 font-semibold text-slate-900">Your cart is empty</h2>
          <p className="mb-5 text-sm text-gray-500">Add something you like and it will show up here.</p>
          <Link href="/shop" className="inline-block rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
            Browse the shop
          </Link>
        </div>
      ) : !quote ? (
        <div className="rounded-2xl border border-gray-100 bg-white p-12 text-center text-sm text-gray-400 shadow-sm">Loading your cart…</div>
      ) : (
        <div className={`grid items-start gap-6 lg:grid-cols-[1fr_380px] ${loading ? "opacity-70 transition-opacity" : ""}`}>
          <div className="min-w-0 space-y-5">
            {quote.free_shipping_remaining > 0 ? (
              <div className="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div className="mb-2 flex items-center gap-2 text-sm text-slate-700">
                  <Truck className="h-4 w-4 text-blue-600" />
                  Add <span className="font-semibold">{money(quote.free_shipping_remaining)}</span> more for free shipping
                </div>
                <div className="h-2 overflow-hidden rounded-full bg-gray-100">
                  <div
                    className="h-full rounded-full bg-blue-600"
                    style={{ width: `${Math.min(100, Math.round((quote.subtotal / quote.free_shipping_threshold) * 100))}%` }}
                  />
                </div>
              </div>
            ) : (
              <div className="flex items-center gap-2 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
                <PartyPopper className="h-4 w-4" /> You&apos;ve unlocked free shipping.
              </div>
            )}

            <div className="divide-y divide-gray-100 rounded-2xl border border-gray-100 bg-white shadow-sm">
              {quote.items.map(({ product: p, qty, subtotal }) => {
                const lineMax = Math.max(1, p.max_qty);
                return (
                  <div key={p.id} className="flex gap-4 p-5 sm:gap-6">
                    <Link href={`/shop/${p.slug}`} className="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-50 sm:h-32 sm:w-32">
                      {/* eslint-disable-next-line @next/next/no-img-element */}
                      <img src={p.image} alt={p.name} className="h-full w-full object-contain p-2" />
                    </Link>
                    <div className="flex min-w-0 flex-1 flex-col">
                      <div className="flex items-start justify-between gap-3">
                        <div className="min-w-0">
                          <Link href={`/shop/${p.slug}`} className="block truncate text-lg font-semibold text-slate-900 hover:text-blue-600">
                            {p.name}
                          </Link>
                          <span className="mt-1 inline-flex items-center gap-1 rounded-md bg-green-50 px-2 py-0.5 text-xs text-green-700">
                            <Check className="h-3 w-3" /> In Stock
                          </span>
                          <div className="mt-2 text-sm text-gray-500">{money(p.final_price)} each</div>
                        </div>
                        <div className="text-lg font-bold text-slate-900">{money(subtotal)}</div>
                      </div>
                      <div className="mt-auto flex items-end justify-between pt-3">
                        <div className="inline-flex items-center overflow-hidden rounded-lg border border-gray-200">
                          <button
                            type="button"
                            onClick={() => cart.setQty(p.id, qty - 1)}
                            disabled={qty <= 1}
                            aria-label="Decrease quantity"
                            className="flex h-10 w-10 items-center justify-center text-gray-600 hover:bg-gray-50 disabled:text-gray-300 disabled:hover:bg-transparent"
                          >
                            <Minus className="h-4 w-4" />
                          </button>
                          <span className="w-12 text-center text-sm font-medium" aria-live="polite">{qty}</span>
                          <button
                            type="button"
                            onClick={() => cart.setQty(p.id, qty + 1)}
                            disabled={qty >= lineMax}
                            aria-label="Increase quantity"
                            className="flex h-10 w-10 items-center justify-center text-gray-600 hover:bg-gray-50 disabled:text-gray-300 disabled:hover:bg-transparent"
                          >
                            <Plus className="h-4 w-4" />
                          </button>
                        </div>
                        <button
                          type="button"
                          onClick={() => cart.remove(p.id)}
                          aria-label={`Remove ${p.name} from cart`}
                          className="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 text-gray-500 hover:bg-red-50 hover:text-red-600"
                        >
                          <Trash2 className="h-4 w-4" />
                        </button>
                      </div>
                      {qty >= lineMax && <p className="mt-2 text-xs text-gray-400">Maximum {lineMax} per order for this item.</p>}
                    </div>
                  </div>
                );
              })}
            </div>

            <div className="flex flex-col gap-4 rounded-2xl border border-blue-100 bg-blue-50/60 p-5 md:flex-row md:items-center">
              <div className="flex flex-1 items-center gap-4">
                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                  <Ticket className="h-5 w-5" />
                </div>
                <div>
                  <div className="font-semibold text-slate-900">Have a coupon code?</div>
                  <div className="text-sm text-gray-500">Enter your coupon code to get a discount on your order.</div>
                </div>
              </div>
              <CouponForm quote={quote} couponError={couponError} />
            </div>

            <Link href="/shop" className="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700">
              <ArrowLeft className="h-4 w-4" /> Continue Shopping
            </Link>
          </div>

          <aside className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:sticky lg:top-24">
            <h2 className="mb-5 text-xl font-bold text-slate-900">Order Summary</h2>
            <SummaryRows quote={quote} subtotalLabel={`Subtotal (${quote.item_count} ${plural("item", quote.item_count)})`} />
            <div className="mt-4 flex items-baseline justify-between border-t border-gray-100 pt-4">
              <span className="text-lg font-bold text-slate-900">Total</span>
              <span className="text-2xl font-bold text-blue-600">{money(quote.total)}</span>
            </div>
            {user?.is_admin ? (
              <p className="mt-5 rounded-lg bg-yellow-50 p-3 text-sm text-yellow-800">
                Admin accounts cannot place orders. Please use a customer account to checkout.
              </p>
            ) : (
              <Link
                href="/checkout"
                aria-disabled={loading}
                className="mt-5 flex items-center justify-center gap-2 rounded-lg bg-blue-600 py-3.5 font-semibold text-white hover:bg-blue-700"
              >
                <Lock className="h-4 w-4" /> Proceed to Checkout
              </Link>
            )}
            <div className="mt-6 grid grid-cols-2 gap-3 text-xs text-gray-600">
              <div className="flex items-center gap-2"><ShieldCheck className="h-5 w-5 shrink-0 text-gray-500" /> Secure Checkout</div>
              <div className="flex items-center gap-2"><Truck className="h-5 w-5 shrink-0 text-gray-500" /> Fast Delivery</div>
              <div className="flex items-center gap-2"><RefreshCw className="h-5 w-5 shrink-0 text-gray-500" /> Easy Returns</div>
              <div className="flex items-center gap-2"><Headphones className="h-5 w-5 shrink-0 text-gray-500" /> 24/7 Support</div>
            </div>
          </aside>
        </div>
      )}
    </div>
  );
}
