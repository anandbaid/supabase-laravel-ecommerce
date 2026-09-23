import Link from "next/link";
import { ChevronLeft, ChevronRight } from "lucide-react";
import type { PaginationMeta } from "@/lib/types";

/** Page links that keep the current query string (filters, sort). */
export function Pagination({
  meta,
  basePath,
  query = {},
  pageParam = "page",
}: {
  meta: PaginationMeta;
  basePath: string;
  query?: Record<string, string | undefined>;
  pageParam?: string;
}) {
  if (meta.last_page <= 1) return null;

  const href = (page: number) => {
    const params = new URLSearchParams();
    for (const [k, v] of Object.entries(query)) if (v) params.set(k, v);
    if (page > 1) params.set(pageParam, String(page));
    const qs = params.toString();
    return qs ? `${basePath}?${qs}` : basePath;
  };

  const current = meta.current_page;
  const pages: (number | "…")[] = [];
  for (let p = 1; p <= meta.last_page; p++) {
    if (p === 1 || p === meta.last_page || Math.abs(p - current) <= 1) pages.push(p);
    else if (pages[pages.length - 1] !== "…") pages.push("…");
  }

  const box = "flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm";

  return (
    <nav className="flex flex-wrap items-center justify-center gap-1.5" aria-label="Pagination">
      {current > 1 ? (
        <Link href={href(current - 1)} className={`${box} bg-white hover:bg-gray-50`} aria-label="Previous page">
          <ChevronLeft className="h-4 w-4" />
        </Link>
      ) : (
        <span className={`${box} bg-gray-50 text-gray-300`} aria-hidden><ChevronLeft className="h-4 w-4" /></span>
      )}
      {pages.map((p, i) =>
        p === "…" ? (
          <span key={`gap-${i}`} className="px-1 text-gray-400">…</span>
        ) : (
          <Link
            key={p}
            href={href(p)}
            aria-current={p === current ? "page" : undefined}
            className={`${box} ${p === current ? "border-blue-600 bg-blue-600 text-white" : "bg-white hover:bg-gray-50"}`}
          >
            {p}
          </Link>
        ),
      )}
      {current < meta.last_page ? (
        <Link href={href(current + 1)} className={`${box} bg-white hover:bg-gray-50`} aria-label="Next page">
          <ChevronRight className="h-4 w-4" />
        </Link>
      ) : (
        <span className={`${box} bg-gray-50 text-gray-300`} aria-hidden><ChevronRight className="h-4 w-4" /></span>
      )}
    </nav>
  );
}
