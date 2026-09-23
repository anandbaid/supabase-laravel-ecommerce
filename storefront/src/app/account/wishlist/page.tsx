import type { Metadata } from "next";
import Link from "next/link";
import { Heart } from "lucide-react";
import { ProductGrid } from "@/components/shop/product-card";
import { Pagination } from "@/components/ui/pagination";
import { api } from "@/lib/api";
import { plural } from "@/lib/format";
import type { Paginated, Product } from "@/lib/types";

export const metadata: Metadata = { title: "My Wishlist" };

export default async function WishlistPage({ searchParams }: PageProps<"/account/wishlist">) {
  const page = Number((await searchParams).page) || 1;
  const res = await api<Paginated<Product>>(`/wishlist?page=${page}`);
  if (!res.ok) throw new Error(res.message);
  const { data: products, meta } = res.data;

  return (
    <>
      <div className="mb-6 flex items-center gap-3">
        <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-500"><Heart className="h-6 w-6" fill="currentColor" /></div>
        <div>
          <h2 className="text-2xl font-bold text-slate-900">My Wishlist</h2>
          <p className="text-sm text-gray-500">{meta.total} {plural("item", meta.total)} saved</p>
        </div>
      </div>
      {products.length === 0 ? (
        <div className="rounded-2xl border border-gray-100 bg-white p-12 text-center shadow-sm">
          <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-50 text-red-400"><Heart className="h-7 w-7" /></div>
          <h3 className="mb-1 font-semibold text-slate-900">Your wishlist is empty</h3>
          <p className="mb-5 text-sm text-gray-500">Tap the heart on any product to save it here for later.</p>
          <Link href="/shop" className="inline-block rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-700">Browse the shop</Link>
        </div>
      ) : (
        <>
          <ProductGrid products={products} className="grid grid-cols-2 gap-5 lg:grid-cols-3" />
          <div className="mt-6"><Pagination meta={meta} basePath="/account/wishlist" /></div>
        </>
      )}
    </>
  );
}
