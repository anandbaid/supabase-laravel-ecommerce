import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { ArrowUpDown, DollarSign, LayoutGrid, Package, PackageSearch, Search, SlidersHorizontal } from "lucide-react";
import { ProductListing, SortSelect } from "@/components/shop/listing-controls";
import { Pagination } from "@/components/ui/pagination";
import { api } from "@/lib/api";
import { getCategoryTree } from "@/lib/data";
import type { Paginated, Product } from "@/lib/types";

export const metadata: Metadata = { title: "Shop" };

const FILTER_KEYS = ["search", "category", "deals", "min_price", "max_price", "sort"] as const;

export default async function ShopPage({ searchParams }: PageProps<"/shop">) {
  const raw = await searchParams;
  const one = (v: string | string[] | undefined) => (Array.isArray(v) ? v[0] : v) || undefined;
  const filters = Object.fromEntries(FILTER_KEYS.map((k) => [k, one(raw[k])])) as Record<(typeof FILTER_KEYS)[number], string | undefined>;
  const page = one(raw.page);

  const qs = new URLSearchParams();
  for (const [k, v] of Object.entries(filters)) if (v) qs.set(k, v);
  if (page) qs.set("page", page);

  const [res, categories] = await Promise.all([
    api<Paginated<Product>>(`/products?${qs}`, { auth: false, revalidate: 15 }),
    getCategoryTree(),
  ]);

  const input = "w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500";

  return (
    <>
      <section className="relative overflow-hidden bg-blue-50">
        <Image src="/images/shop-banner.png" alt="" fill sizes="100vw" className="pointer-events-none select-none object-cover object-right" />
        <div className="relative mx-auto max-w-7xl px-4 py-14 md:py-16">
          <div className="max-w-xl">
            <h1 className="mb-3 text-4xl font-extrabold text-gray-900 md:text-5xl">
              {filters.deals ? "Today's " : "Our "}
              <span className="text-blue-600">{filters.deals ? "Deals" : "Products"}</span>
            </h1>
            <p className="text-gray-600">
              Discover amazing products at the best prices. <br />
              Shop your favorites and enjoy a better experience.
            </p>
          </div>
        </div>
      </section>

      <div className="mx-auto grid max-w-7xl grid-cols-1 gap-8 px-4 py-8 md:grid-cols-4">
        <aside>
          <form action="/shop" className="space-y-5 rounded-xl bg-white p-5 shadow-sm">
            {filters.deals && <input type="hidden" name="deals" value={filters.deals} />}
            <div className="flex items-center justify-between border-b pb-3">
              <h3 className="flex items-center gap-2 text-sm font-semibold">
                <SlidersHorizontal className="h-4 w-4 text-blue-600" /> Filter Products
              </h3>
              <Link href="/shop" className="text-xs text-blue-600 hover:underline">Clear All</Link>
            </div>

            <label className="block">
              <span className="mb-2 flex items-center gap-1.5 text-sm font-semibold"><Search className="h-4 w-4 text-gray-400" /> Search</span>
              <input type="text" name="search" defaultValue={filters.search} placeholder="Search products..." className={input} />
            </label>

            <label className="block">
              <span className="mb-2 flex items-center gap-1.5 text-sm font-semibold"><LayoutGrid className="h-4 w-4 text-gray-400" /> Category</span>
              <select name="category" defaultValue={filters.category ?? ""} className={input}>
                <option value="">All Categories</option>
                {categories.map((cat) =>
                  cat.children?.length ? (
                    <optgroup key={cat.id} label={cat.name}>
                      <option value={cat.slug}>All {cat.name}</option>
                      {cat.children.map((child) => (
                        <option key={child.id} value={child.slug}>— {child.name}</option>
                      ))}
                    </optgroup>
                  ) : (
                    <option key={cat.id} value={cat.slug}>{cat.name}</option>
                  ),
                )}
              </select>
            </label>

            <div>
              <span className="mb-2 flex items-center gap-1.5 text-sm font-semibold"><DollarSign className="h-4 w-4 text-gray-400" /> Price Range</span>
              <div className="flex items-center gap-2">
                <input type="number" min={0} name="min_price" defaultValue={filters.min_price} placeholder="Min" aria-label="Minimum price" className={input} />
                <span className="text-gray-400">—</span>
                <input type="number" min={0} name="max_price" defaultValue={filters.max_price} placeholder="Max" aria-label="Maximum price" className={input} />
              </div>
            </div>

            <label className="block">
              <span className="mb-2 flex items-center gap-1.5 text-sm font-semibold"><ArrowUpDown className="h-4 w-4 text-gray-400" /> Sort By</span>
              <select name="sort" defaultValue={filters.sort ?? "latest"} className={input}>
                <option value="latest">Latest</option>
                <option value="price_low">Price: Low to High</option>
                <option value="price_high">Price: High to Low</option>
                <option value="name">Name</option>
              </select>
            </label>

            <button className="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">
              <SlidersHorizontal className="h-4 w-4" /> Apply Filters
            </button>
          </form>
        </aside>

        <div className="md:col-span-3">
          {!res.ok ? (
            <p className="rounded-xl bg-white p-8 text-center text-sm text-red-600 shadow-sm">{res.message}</p>
          ) : (
            <ProductListing
              toolbar={
                <>
                  <p className="flex items-center gap-2 text-sm text-gray-500">
                    <Package className="h-4 w-4 text-blue-600" /> {res.data.meta.total} products found
                  </p>
                  <SortSelect value={filters.sort ?? "latest"} />
                </>
              }
              products={res.data.data}
              empty={
                <div className="col-span-full py-16 text-center text-gray-400">
                  <PackageSearch className="mx-auto mb-3 h-10 w-10" />
                  No products found.
                </div>
              }
              pagination={<Pagination meta={res.data.meta} basePath="/shop" query={filters} />}
            />
          )}
        </div>
      </div>
    </>
  );
}
