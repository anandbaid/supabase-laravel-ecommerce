"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import { CircleCheck, CircleX, Minus, Plus, Zap } from "lucide-react";
import { useCart } from "@/components/providers/cart";
import { useSession } from "@/components/providers/session";
import { useToast } from "@/components/providers/toast";
import type { ProductDetail } from "@/lib/types";
import { AddToCartButton } from "./add-to-cart-button";
import { useLiveStock } from "./live-stock";
import { WishlistButton } from "./wishlist-button";

export function BuyBox({ product }: { product: ProductDetail }) {
  const router = useRouter();
  const cart = useCart();
  const { user } = useSession();
  const toast = useToast();
  const live = useLiveStock(product.id, product.stock, product.max_qty);
  const maxQty = Math.max(1, live.maxQty);
  const inStock = live.stock > 0;
  const [qty, setQty] = useState(1);
  const clampedQty = Math.min(qty, maxQty);

  const buyNow = () => {
    if (user?.is_admin) {
      toast("Admin accounts cannot place orders. Please use a customer account to checkout.", "error");
      return;
    }
    cart.set({ ...product, max_qty: live.maxQty }, clampedQty);
    router.push("/checkout");
  };

  return (
    <>
      <p className={`mb-1 flex items-center gap-1.5 text-sm ${inStock ? "text-green-600" : "text-red-600"}`}>
        {inStock ? <CircleCheck className="h-4 w-4" /> : <CircleX className="h-4 w-4" />}
        {inStock ? `${live.stock} in stock` : "Out of stock"}
        <span className="ml-2 text-gray-400">SKU: {product.sku}</span>
      </p>

      <div className="mt-5">
        <label htmlFor="qty-input" className="mb-2 block text-sm font-medium text-gray-700">Quantity</label>
        <div className="mb-5 flex items-center gap-3">
          <div className="inline-flex items-center overflow-hidden rounded-lg border border-gray-200">
            <button type="button" onClick={() => setQty(Math.max(1, clampedQty - 1))} aria-label="Decrease quantity" className="flex h-10 w-10 items-center justify-center text-gray-600 hover:bg-gray-50">
              <Minus className="h-4 w-4" />
            </button>
            <input
              id="qty-input"
              type="number"
              min={1}
              max={maxQty}
              value={clampedQty}
              onChange={(e) => setQty(Math.min(maxQty, Math.max(1, Number(e.target.value) || 1)))}
              className="h-10 w-14 border-0 border-x border-gray-200 text-center text-sm [appearance:textfield] focus:ring-0 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
            />
            <button type="button" onClick={() => setQty(Math.min(maxQty, clampedQty + 1))} aria-label="Increase quantity" className="flex h-10 w-10 items-center justify-center text-gray-600 hover:bg-gray-50">
              <Plus className="h-4 w-4" />
            </button>
          </div>
          <span className="text-xs text-gray-400">(Max {maxQty})</span>
        </div>

        <div className="grid grid-cols-2 gap-3">
          <AddToCartButton
            product={{ ...product, max_qty: live.maxQty }}
            qty={clampedQty}
            className="rounded-lg border border-blue-600 px-5 py-3 font-medium text-blue-600 hover:bg-blue-50 disabled:border-gray-200 disabled:text-gray-400 disabled:hover:bg-transparent"
          />
          <button
            type="button"
            onClick={buyNow}
            disabled={!inStock}
            className="flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-3 font-medium text-white hover:bg-blue-700 disabled:bg-gray-200 disabled:text-gray-400"
          >
            <Zap className="h-4 w-4" /> Buy Now
          </button>
        </div>
      </div>

      <WishlistButton productId={product.id} variant="full" />
    </>
  );
}
