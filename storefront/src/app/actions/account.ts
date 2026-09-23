"use server";

import { api, failure } from "@/lib/api";
import type { ActionResult, Address, Order, User } from "@/lib/types";

export type CheckoutPayload = {
  items: { product_id: number; qty: number }[];
  coupon_code: string | null;
  [field: string]: unknown;
};

export async function placeOrder(
  payload: CheckoutPayload,
): Promise<ActionResult<{ order_number: string; checkout_url: string | null }>> {
  const res = await api<{ order_number: string; checkout_url: string | null }>("/checkout", {
    method: "POST",
    body: payload,
  });
  return res.ok ? { ok: true, data: res.data } : failure(res);
}

export async function updateProfile(_prev: ActionResult<User> | null, form: FormData): Promise<ActionResult<User>> {
  const password = String(form.get("password") ?? "");
  const res = await api<{ message: string; user: User }>("/me", {
    method: "PATCH",
    body: {
      name: form.get("name"),
      email: form.get("email"),
      phone: form.get("phone") || null,
      address: form.get("address") || null,
      password: password || null,
      password_confirmation: password ? form.get("password_confirmation") : null,
    },
  });
  return res.ok ? { ok: true, message: res.data.message, data: res.data.user } : failure(res);
}

export async function saveAddress(id: number | null, input: Record<string, unknown>): Promise<ActionResult<Address>> {
  const res = await api<{ data: Address }>(id ? `/addresses/${id}` : "/addresses", {
    method: id ? "PUT" : "POST",
    body: input,
  });
  return res.ok
    ? { ok: true, message: id ? "Address updated." : "Address added.", data: res.data.data }
    : failure(res);
}

export async function deleteAddress(id: number): Promise<ActionResult> {
  const res = await api<{ message: string }>(`/addresses/${id}`, { method: "DELETE" });
  return res.ok ? { ok: true, message: res.data.message, data: null } : failure(res);
}

export async function makeDefaultAddress(id: number): Promise<ActionResult> {
  const res = await api(`/addresses/${id}/default`, { method: "POST" });
  return res.ok ? { ok: true, message: "Default address updated.", data: null } : failure(res);
}

export async function cancelOrder(orderNumber: string, reason: string): Promise<ActionResult<Order>> {
  const res = await api<{ message: string; order: Order }>(`/orders/${encodeURIComponent(orderNumber)}/cancel`, {
    method: "POST",
    body: { reason: reason || null },
  });
  return res.ok ? { ok: true, message: res.data.message, data: res.data.order } : failure(res);
}

export async function requestReturn(orderNumber: string, reason: string): Promise<ActionResult<Order>> {
  const res = await api<{ message: string; order: Order }>(`/orders/${encodeURIComponent(orderNumber)}/return`, {
    method: "POST",
    body: { reason },
  });
  return res.ok ? { ok: true, message: res.data.message, data: res.data.order } : failure(res);
}
