"use client";

import { useEffect, useState } from "react";
import { createClient, type RealtimeChannel } from "@supabase/supabase-js";

const MAX_PER_ITEM = 10;

/**
 * Live stock for one product via Supabase Realtime (the same `products`
 * change feed the Blade site listens to). Needs NEXT_PUBLIC_SUPABASE_URL
 * and NEXT_PUBLIC_SUPABASE_ANON_KEY and Realtime replication enabled on the
 * `products` table; without them the page just shows the stock it loaded with.
 */
export function useLiveStock(productId: number, initialStock: number, initialMaxQty: number) {
  const [stock, setStock] = useState(initialStock);

  useEffect(() => {
    const url = process.env.NEXT_PUBLIC_SUPABASE_URL;
    const key = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY;
    if (!url || !key) return;

    let channel: RealtimeChannel | null = null;
    const supabase = createClient(url, key, { auth: { persistSession: false } });
    try {
      channel = supabase
        .channel(`product-stock-${productId}`)
        .on(
          "postgres_changes",
          { event: "UPDATE", schema: "public", table: "products", filter: `id=eq.${productId}` },
          (payload) => {
            const next = (payload.new as { stock?: number }).stock;
            if (typeof next === "number") setStock(next);
          },
        )
        .subscribe();
    } catch (e) {
      console.error("Supabase Realtime subscription failed", e);
    }
    return () => {
      if (channel) supabase.removeChannel(channel);
    };
  }, [productId]);

  const maxQty = stock === initialStock ? initialMaxQty : Math.max(0, Math.min(MAX_PER_ITEM, stock));
  return { stock, maxQty };
}
