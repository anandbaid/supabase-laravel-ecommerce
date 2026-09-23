import Link from "next/link";
import { Flame } from "lucide-react";
import { money } from "@/lib/format";
import type { Product } from "@/lib/types";
import { AddToCartButton } from "./add-to-cart-button";
import { WishlistButton } from "./wishlist-button";

export function ProductCard({ product }: { product: Product }) {
  return (
    <div className="relative flex flex-col rounded-xl bg-white p-4 shadow-sm transition hover:shadow-md">
      {product.discount_percent ? (
        <span className="absolute left-3 top-3 z-10 rounded-full bg-red-500 px-2 py-0.5 text-xs text-white">
          -{product.discount_percent}%
        </span>
      ) : null}
      <WishlistButton productId={product.id} />
      <Link href={`/shop/${product.slug}`}>
        <div className="mb-3 flex h-32 items-center justify-center">
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img src={product.image} alt={product.name} loading="lazy" className="max-h-32 object-contain" />
        </div>
        <div className="line-clamp-2 text-sm font-medium text-gray-800">{product.name}</div>
      </Link>
      <div className="mt-2 flex items-center gap-2">
        <span className="font-semibold text-blue-600">{money(product.final_price)}</span>
        {product.discount_price !== null && (
          <span className="text-xs text-gray-400 line-through">{money(product.price)}</span>
        )}
      </div>
      {product.stock > 0 && product.stock <= 5 && (
        <p className="mt-1 flex items-center gap-1 text-xs font-medium text-orange-600">
          <Flame className="h-3 w-3" /> Only {product.stock} left!
        </p>
      )}
      <div className="mt-auto pt-3">
        <AddToCartButton
          product={product}
          className="w-full rounded-lg bg-blue-600 py-2 text-sm text-white hover:bg-blue-700 disabled:bg-gray-200 disabled:text-gray-500"
        />
      </div>
    </div>
  );
}

export function ProductGrid({ products, className }: { products: Product[]; className: string }) {
  return (
    <div className={className}>
      {products.map((p) => (
        <ProductCard key={p.id} product={p} />
      ))}
    </div>
  );
}
