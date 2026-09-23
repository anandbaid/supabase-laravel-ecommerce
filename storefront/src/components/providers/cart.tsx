"use client";

import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from "react";
import type { CartQuote } from "@/lib/types";

/**
 * The cart lives in the browser (localStorage), so guests can shop without
 * an account and nothing is written server-side until checkout. Every price
 * shown comes from a server quote; this only remembers product ids,
 * quantities and the coupon code.
 */

type CartState = { items: Record<number, number>; coupon: string | null };

type AddableProduct = { id: number; name: string; max_qty: number };
type AddResult = { ok: boolean; message: string; capped: boolean };

type CartContextValue = {
  hydrated: boolean;
  items: Record<number, number>;
  coupon: string | null;
  count: number;
  add: (product: AddableProduct, qty?: number) => AddResult;
  /** Put exactly this quantity in the cart ("Buy now"). */
  set: (product: AddableProduct, qty: number) => void;
  setQty: (productId: number, qty: number) => void;
  remove: (productId: number) => void;
  clear: () => void;
  setCoupon: (code: string | null) => void;
  /** Adopt the server's view after a quote (drops unavailable items, clamps quantities). */
  syncWithQuote: (quote: CartQuote) => void;
};

const STORAGE_KEY = "ls_cart";
const EMPTY: CartState = { items: {}, coupon: null };

const CartContext = createContext<CartContextValue | null>(null);

export function useCart() {
  const ctx = useContext(CartContext);
  if (!ctx) throw new Error("useCart must be used inside <CartProvider>");
  return ctx;
}

function read(): CartState {
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY);
    if (!raw) return EMPTY;
    const parsed = JSON.parse(raw) as Partial<CartState>;
    const items: Record<number, number> = {};
    for (const [id, qty] of Object.entries(parsed.items ?? {})) {
      const n = Math.floor(Number(qty));
      if (Number(id) > 0 && n > 0) items[Number(id)] = Math.min(n, 999);
    }
    return { items, coupon: typeof parsed.coupon === "string" ? parsed.coupon : null };
  } catch {
    return EMPTY;
  }
}

function write(state: CartState) {
  try {
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
  } catch {
    // Private mode / storage full: the cart still works for this tab.
  }
}

export function CartProvider({ children }: { children: ReactNode }) {
  const [state, setState] = useState<CartState>(EMPTY);
  const [hydrated, setHydrated] = useState(false);

  useEffect(() => {
    // Loading from localStorage has to wait until after hydration.
    // eslint-disable-next-line react-hooks/set-state-in-effect
    setState(read());
    setHydrated(true);
    const onStorage = (e: StorageEvent) => {
      if (e.key === STORAGE_KEY) setState(read());
    };
    window.addEventListener("storage", onStorage);
    return () => window.removeEventListener("storage", onStorage);
  }, []);

  const update = useCallback((fn: (s: CartState) => CartState) => {
    setState((prev) => {
      const next = fn(prev);
      write(next);
      return next;
    });
  }, []);

  const add = useCallback(
    (product: AddableProduct, qty = 1): AddResult => {
      const max = product.max_qty;
      if (max < 1) return { ok: false, capped: false, message: `${product.name} is out of stock.` };
      const current = read().items[product.id] ?? 0;
      const wanted = current + Math.max(1, qty);
      const capped = wanted > max;
      update((s) => ({ ...s, items: { ...s.items, [product.id]: Math.min(wanted, max) } }));
      return {
        ok: true,
        capped,
        message: capped
          ? `You can buy up to ${max} of ${product.name}. Your cart has been set to ${max}.`
          : `${product.name} added to cart.`,
      };
    },
    [update],
  );

  const set = useCallback(
    (product: AddableProduct, qty: number) => {
      update((s) => ({
        ...s,
        items: { ...s.items, [product.id]: Math.min(Math.max(1, qty), Math.max(1, product.max_qty)) },
      }));
    },
    [update],
  );

  const setQty = useCallback(
    (productId: number, qty: number) =>
      update((s) => (s.items[productId] ? { ...s, items: { ...s.items, [productId]: Math.max(1, qty) } } : s)),
    [update],
  );

  const remove = useCallback(
    (productId: number) =>
      update((s) => {
        const items = { ...s.items };
        delete items[productId];
        return { ...s, items };
      }),
    [update],
  );

  const clear = useCallback(() => update(() => EMPTY), [update]);

  const setCoupon = useCallback((code: string | null) => update((s) => ({ ...s, coupon: code })), [update]);

  const syncWithQuote = useCallback(
    (quote: CartQuote) =>
      update((s) => {
        const items: Record<number, number> = {};
        for (const line of quote.items) items[line.product.id] = line.qty;
        const coupon = s.coupon && quote.coupon_error ? null : s.coupon;
        const same =
          coupon === s.coupon &&
          Object.keys(items).length === Object.keys(s.items).length &&
          Object.entries(items).every(([id, q]) => s.items[Number(id)] === q);
        return same ? s : { items, coupon };
      }),
    [update],
  );

  const value = useMemo<CartContextValue>(
    () => ({
      hydrated,
      items: state.items,
      coupon: state.coupon,
      count: Object.values(state.items).reduce((a, b) => a + b, 0),
      add,
      set,
      setQty,
      remove,
      clear,
      setCoupon,
      syncWithQuote,
    }),
    [hydrated, state, add, set, setQty, remove, clear, setCoupon, syncWithQuote],
  );

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}
