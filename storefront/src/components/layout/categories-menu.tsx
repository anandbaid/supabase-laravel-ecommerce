"use client";

import Link from "next/link";
import { useCallback, useRef, useState } from "react";
import { ChevronDown } from "lucide-react";
import type { Category } from "@/lib/types";
import { useDismiss } from "./use-dismiss";

export function CategoriesMenu({ categories }: { categories: Category[] }) {
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);
  const close = useCallback(() => setOpen(false), []);
  useDismiss(ref, open, close);

  return (
    <div className="relative" ref={ref}>
      <button
        type="button"
        onClick={() => setOpen((o) => !o)}
        aria-haspopup="true"
        aria-expanded={open}
        className="flex items-center gap-1 text-gray-700 hover:text-blue-600"
      >
        Categories <ChevronDown className="h-3.5 w-3.5" />
      </button>
      {open && (
        <div className="absolute left-0 top-full z-20 pt-3" onClick={close}>
          <div className="grid w-[420px] grid-cols-2 gap-x-8 gap-y-4 rounded-xl border bg-white p-4 shadow-lg">
            {categories.length === 0 && <p className="col-span-2 text-sm text-gray-400">No categories yet.</p>}
            {categories.map((cat) => (
              <div key={cat.id}>
                <Link href={`/shop?category=${cat.slug}`} className="text-sm font-semibold text-gray-800 hover:text-blue-600">
                  {cat.name}
                </Link>
                {!!cat.children?.length && (
                  <ul className="mt-1.5 space-y-1">
                    {cat.children.map((child) => (
                      <li key={child.id}>
                        <Link href={`/shop?category=${child.slug}`} className="text-xs text-gray-500 hover:text-blue-600">
                          {child.name}
                        </Link>
                      </li>
                    ))}
                  </ul>
                )}
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
