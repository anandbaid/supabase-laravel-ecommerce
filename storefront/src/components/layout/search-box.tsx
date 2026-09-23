"use client";

import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { Suspense, useCallback, useEffect, useRef, useState } from "react";
import { Search } from "lucide-react";
import { searchSuggestions } from "@/app/actions/shop";
import { money } from "@/lib/format";
import type { Product } from "@/lib/types";
import { useDismiss } from "./use-dismiss";

function SearchBoxInner() {
  const router = useRouter();
  const params = useSearchParams();
  const [term, setTerm] = useState(params.get("search") ?? "");
  const [results, setResults] = useState<Product[]>([]);
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLFormElement>(null);
  const close = useCallback(() => setOpen(false), []);
  useDismiss(ref, open, close);

  useEffect(() => {
    const q = term.trim();
    if (q.length < 2) return;
    let cancelled = false;
    const timer = window.setTimeout(async () => {
      const found = await searchSuggestions(q);
      if (!cancelled) {
        setResults(found);
        setOpen(true);
      }
    }, 250);
    return () => {
      cancelled = true;
      window.clearTimeout(timer);
    };
  }, [term]);

  const showResults = open && term.trim().length >= 2 && results.length > 0;

  return (
    <form
      ref={ref}
      role="search"
      className="relative hidden max-w-sm flex-1 md:flex"
      autoComplete="off"
      onSubmit={(e) => {
        e.preventDefault();
        setOpen(false);
        router.push(term.trim() ? `/shop?search=${encodeURIComponent(term.trim())}` : "/shop");
      }}
    >
      <input
        type="text"
        value={term}
        onChange={(e) => setTerm(e.target.value)}
        onFocus={() => setOpen(true)}
        placeholder="Search for products..."
        aria-label="Search for products"
        className="w-full rounded-l-full border border-gray-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
      />
      <button className="flex items-center justify-center rounded-r-full bg-blue-600 px-4 text-white" aria-label="Search">
        <Search className="h-4 w-4" />
      </button>
      {showResults && (
        <div className="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-xl border bg-white shadow-lg">
          {results.map((p) => (
            <Link
              key={p.id}
              href={`/shop/${p.slug}`}
              onClick={() => setOpen(false)}
              className="flex items-center gap-3 px-3 py-2 hover:bg-gray-50"
            >
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img src={p.image_small} alt="" className="h-10 w-10 rounded bg-gray-50 object-contain" />
              <span className="flex-1 truncate text-sm text-gray-800">{p.name}</span>
              <span className="text-sm font-semibold text-blue-600">{money(p.final_price)}</span>
            </Link>
          ))}
        </div>
      )}
    </form>
  );
}

export function SearchBox() {
  return (
    <Suspense fallback={<div className="hidden max-w-sm flex-1 md:flex" />}>
      <SearchBoxInner />
    </Suspense>
  );
}
