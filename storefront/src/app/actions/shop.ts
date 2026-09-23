"use server";

import { api, failure } from "@/lib/api";
import { getAccessToken } from "@/lib/session";
import type { ActionResult, CartQuote, Product, Review } from "@/lib/types";

export type CartItemInput = { product_id: number; qty: number };

/** Price the browser-held cart. Prices, stock and coupons are checked by Laravel. */
export async function quoteCart(items: CartItemInput[], couponCode: string | null): Promise<ActionResult<CartQuote>> {
  const res = await api<CartQuote>("/cart/quote", {
    method: "POST",
    auth: false,
    body: { items, coupon_code: couponCode || null },
  });
  return res.ok ? { ok: true, data: res.data } : failure(res);
}

export async function searchSuggestions(q: string): Promise<Product[]> {
  if (q.trim().length < 2) return [];
  const res = await api<{ data: Product[] }>(`/products/suggest?q=${encodeURIComponent(q.trim())}`, { auth: false });
  return res.ok ? res.data.data : [];
}

export async function toggleWishlist(
  productId: number,
): Promise<ActionResult<{ wishlisted: boolean; count: number }>> {
  if (!(await getAccessToken())) {
    return { ok: false, status: 401, message: "Please log in to save items to your wishlist." };
  }
  const res = await api<{ wishlisted: boolean; count: number; message: string }>(`/wishlist/${productId}/toggle`, {
    method: "POST",
  });
  return res.ok
    ? { ok: true, message: res.data.message, data: { wishlisted: res.data.wishlisted, count: res.data.count } }
    : failure(res);
}

export async function saveReview(
  slug: string,
  input: { rating: number; title: string; body: string },
): Promise<ActionResult<Review>> {
  const res = await api<{ message: string; review: Review }>(`/products/${encodeURIComponent(slug)}/reviews`, {
    method: "POST",
    body: input,
  });
  return res.ok ? { ok: true, message: res.data.message, data: res.data.review } : failure(res);
}

export async function deleteReview(reviewId: number): Promise<ActionResult> {
  const res = await api<{ message: string }>(`/reviews/${reviewId}`, { method: "DELETE" });
  return res.ok ? { ok: true, message: res.data.message, data: null } : failure(res);
}

export async function subscribeNewsletter(_prev: ActionResult | null, form: FormData): Promise<ActionResult> {
  const res = await api<{ message: string }>("/newsletter", {
    method: "POST",
    auth: false,
    body: { email: form.get("email") },
  });
  return res.ok ? { ok: true, message: res.data.message, data: null } : failure(res);
}

export async function sendContactMessage(_prev: ActionResult | null, form: FormData): Promise<ActionResult> {
  const res = await api<{ message: string }>("/contact", {
    method: "POST",
    auth: false,
    body: { name: form.get("name"), email: form.get("email"), message: form.get("message") },
  });
  return res.ok ? { ok: true, message: res.data.message, data: null } : failure(res);
}
