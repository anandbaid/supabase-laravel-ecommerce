"use client";

import { useEffect, useRef, useState } from "react";
import { quoteCart } from "@/app/actions/shop";
import { useCart } from "@/components/providers/cart";
import { useToast } from "@/components/providers/toast";
import type { CartQuote } from "@/lib/types";

/**
 * Keeps a server-priced quote of the browser cart up to date. Items that
 * can't be bought any more are dropped from the stored cart (with a toast),
 * and quantities are clamped to what's in stock.
 */
export function useCartQuote() {
  const cart = useCart();
  const toast = useToast();
  const [quote, setQuote] = useState<CartQuote | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  // Why the last coupon didn't apply. Kept after the code is dropped from the
  // cart, so the message stays visible under the coupon field.
  const [couponError, setCouponError] = useState<string | null>(null);
  const request = useRef(0);

  const key = JSON.stringify([cart.items, cart.coupon]);

  useEffect(() => {
    if (!cart.hydrated) return;
    const id = ++request.current;
    const items = Object.entries(cart.items).map(([product_id, qty]) => ({ product_id: Number(product_id), qty }));

    // eslint-disable-next-line react-hooks/set-state-in-effect
    setLoading(true);
    quoteCart(items, cart.coupon).then((res) => {
      if (id !== request.current) return; // a newer request superseded this one
      setLoading(false);
      if (!res.ok) {
        setError(res.message);
        return;
      }
      setError(null);
      setQuote(res.data);
      if (res.data.coupon_error) setCouponError(res.data.coupon_error);
      else if (res.data.coupon) setCouponError(null);
      for (const n of res.data.notices) toast(n.message, "info");
      cart.syncWithQuote(res.data);
    });
    // `key` captures the cart contents; cart/toast are stable.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [key, cart.hydrated]);

  return { quote, error, couponError, loading: loading || !cart.hydrated };
}
