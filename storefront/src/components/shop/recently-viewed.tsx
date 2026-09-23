"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { History } from "lucide-react";
import { money } from "@/lib/format";
import type { Product } from "@/lib/types";

type Snapshot = { id: number; name: string; slug: string; image: string; price: number };

const KEY = "recently_viewed";
const LIMIT = 12;

function load(): Snapshot[] {
  try {
    const items = JSON.parse(window.localStorage.getItem(KEY) || "[]");
    return Array.isArray(items) ? items.filter((p) => p && typeof p.slug === "string") : [];
  } catch {
    return [];
  }
}

/** Called from the product page: remember this product for the strip below. */
export function TrackRecentlyViewed({ product }: { product: Product }) {
  useEffect(() => {
    const snapshot: Snapshot = {
      id: product.id,
      name: product.name,
      slug: product.slug,
      image: product.image_small,
      price: product.final_price,
    };
    try {
      const rest = load().filter((p) => p.id !== product.id);
      window.localStorage.setItem(KEY, JSON.stringify([snapshot, ...rest].slice(0, LIMIT)));
    } catch {
      // Storage unavailable: nothing to remember.
    }
  }, [product]);
  return null;
}

/** A strip of products this browser looked at recently (works for guests too). */
export function RecentlyViewed({ excludeId }: { excludeId?: number }) {
  const [items, setItems] = useState<Snapshot[]>([]);

  useEffect(() => {
    // Read from localStorage after mount (not available during server rendering).
    // eslint-disable-next-line react-hooks/set-state-in-effect
    setItems(load().filter((p) => p.id !== excludeId).slice(0, 6));
  }, [excludeId]);

  if (!items.length) return null;

  return (
    <section className="mx-auto max-w-7xl px-4 py-8">
      <h2 className="mb-4 flex items-center gap-2 text-xl font-bold">
        <History className="h-5 w-5 text-blue-600" /> Recently Viewed
      </h2>
      <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
        {items.map((p) => (
          <Link key={p.id} href={`/shop/${p.slug}`} className="block rounded-xl bg-white p-4 shadow-sm transition hover:shadow-md">
            <div className="mb-2 flex h-24 items-center justify-center">
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img src={p.image} alt={p.name} className="max-h-24 object-contain" />
            </div>
            <div className="line-clamp-2 text-xs font-medium text-gray-800">{p.name}</div>
            <div className="mt-1 text-sm font-semibold text-blue-600">{money(p.price)}</div>
          </Link>
        ))}
      </div>
    </section>
  );
}
