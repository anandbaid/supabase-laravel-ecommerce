"use client";

import { useState } from "react";
import { useCart } from "@/components/providers/cart";
import type { CartQuote } from "@/lib/types";

export function CouponForm({
  quote,
  couponError,
  compact = false,
}: {
  quote: CartQuote | null;
  couponError: string | null;
  compact?: boolean;
}) {
  const cart = useCart();
  const [code, setCode] = useState("");

  if (quote?.coupon) {
    return (
      <div className={`flex items-center justify-between gap-3 rounded-lg border border-green-200 bg-green-50 text-sm ${compact ? "px-3 py-2" : "px-4 py-2.5 md:w-72"}`}>
        <span className="font-medium text-green-700">{quote.coupon.code} applied</span>
        <button type="button" onClick={() => cart.setCoupon(null)} className="text-xs text-red-500 hover:underline">
          Remove
        </button>
      </div>
    );
  }

  return (
    <div className={compact ? "" : "md:w-96"}>
      <form
        className="flex gap-2"
        onSubmit={(e) => {
          e.preventDefault();
          const value = code.trim().toUpperCase();
          if (value) cart.setCoupon(value);
        }}
      >
        <input
          type="text"
          value={code}
          onChange={(e) => setCode(e.target.value)}
          placeholder="Enter coupon code"
          aria-label="Coupon code"
          className="min-w-0 flex-1 rounded-lg border border-gray-200 px-3 py-2.5 text-sm uppercase placeholder:normal-case focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
        />
        <button className="rounded-lg bg-blue-600 px-5 text-sm font-medium text-white hover:bg-blue-700">Apply</button>
      </form>
      {couponError && <p className="mt-1.5 text-xs text-red-600" role="alert">{couponError}</p>}
    </div>
  );
}
