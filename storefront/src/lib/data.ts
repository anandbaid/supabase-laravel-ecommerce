import "server-only";

import { cache } from "react";
import { api } from "./api";
import { getAccessToken } from "./session";
import type { Category, Settings, User } from "./types";

export type Me = { user: User; wishlist_ids: number[] };

/** The signed-in customer (deduplicated per request), or null for guests. */
export const getMe = cache(async (): Promise<Me | null> => {
  if (!(await getAccessToken())) return null;
  const res = await api<Me>("/me");
  return res.ok ? res.data : null;
});

export const getSettings = cache(async (): Promise<Settings> => {
  const res = await api<Settings>("/settings", { auth: false, revalidate: 60 });
  return res.ok
    ? res.data
    : { tax_rate: 0, shipping_fee: 2, free_shipping_threshold: 50, max_qty_per_item: 10, currency: "USD" };
});

export const getCategoryTree = cache(async (): Promise<Category[]> => {
  const res = await api<{ data: Category[] }>("/categories", { auth: false, revalidate: 60 });
  return res.ok ? res.data.data : [];
});
