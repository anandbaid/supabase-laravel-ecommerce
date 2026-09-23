"use client";

import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { useState, type ReactNode } from "react";
import { LayoutGrid, List } from "lucide-react";
import type { Product } from "@/lib/types";
import { ProductCard } from "./product-card";

export function SortSelect({ value }: { value: string }) {
  const router = useRouter();
  const pathname = usePathname();
  const params = useSearchParams();

  return (
    <label className="flex items-center gap-2">
      <span className="hidden text-sm text-gray-500 sm:block">Sort by:</span>
      <select
        value={value}
        onChange={(e) => {
          const next = new URLSearchParams(params.toString());
          next.set("sort", e.target.value);
          next.delete("page");
          router.push(`${pathname}?${next}`);
        }}
        className="rounded-full border border-gray-200 px-3 py-1.5 text-sm outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option value="latest">Latest</option>
        <option value="price_low">Price: Low to High</option>
        <option value="price_high">Price: High to Low</option>
        <option value="name">Name</option>
      </select>
    </label>
  );
}

export function ProductListing({
  toolbar,
  products,
  empty,
  pagination,
}: {
  toolbar: ReactNode;
  products: Product[];
  empty: ReactNode;
  pagination: ReactNode;
}) {
  const [view, setView] = useState<"grid" | "list">("grid");
  const btn = (on: boolean) => `p-2 ${on ? "bg-blue-600 text-white" : "text-gray-500 hover:bg-gray-50"}`;

  return (
    <>
      <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
        {toolbar}
        <div className="flex items-center overflow-hidden rounded-lg border">
          <button type="button" onClick={() => setView("grid")} className={btn(view === "grid")} title="Grid view" aria-pressed={view === "grid"}>
            <LayoutGrid className="h-4 w-4" />
          </button>
          <button type="button" onClick={() => setView("list")} className={btn(view === "list")} title="List view" aria-pressed={view === "list"}>
            <List className="h-4 w-4" />
          </button>
        </div>
      </div>
      <div className={`grid gap-5 ${view === "grid" ? "grid-cols-2 lg:grid-cols-3" : "grid-cols-1"}`}>
        {products.length ? products.map((p) => <ProductCard key={p.id} product={p} />) : empty}
      </div>
      <div className="mt-8">{pagination}</div>
    </>
  );
}
