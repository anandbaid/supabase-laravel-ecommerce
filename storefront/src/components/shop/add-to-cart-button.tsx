"use client";

import { useEffect, useRef, useState } from "react";
import { Check, ShoppingCart } from "lucide-react";
import { useCart } from "@/components/providers/cart";
import { useSession } from "@/components/providers/session";
import { useToast } from "@/components/providers/toast";
import type { Product } from "@/lib/types";

type Props = {
  product: Pick<Product, "id" | "name" | "max_qty">;
  qty?: number;
  className?: string;
  label?: string;
};

export function AddToCartButton({ product, qty = 1, className, label = "Add to Cart" }: Props) {
  const cart = useCart();
  const { user } = useSession();
  const toast = useToast();
  const [added, setAdded] = useState(false);
  const timer = useRef<number | undefined>(undefined);

  useEffect(() => () => window.clearTimeout(timer.current), []);

  const outOfStock = product.max_qty < 1;

  return (
    <button
      type="button"
      disabled={outOfStock || added}
      onClick={() => {
        if (user?.is_admin) {
          toast("Admin accounts cannot place orders. Use a customer account to shop.", "info");
          return;
        }
        const result = cart.add(product, qty);
        toast(result.message, result.ok ? (result.capped ? "info" : "success") : "error", 2500);
        if (result.ok) {
          setAdded(true);
          timer.current = window.setTimeout(() => setAdded(false), 1400);
        }
      }}
      className={`${className ?? ""} ${added ? "!border-green-600 !bg-green-600 !text-white" : ""} flex items-center justify-center gap-2 transition disabled:cursor-not-allowed`}
    >
      {added ? (
        <>
          <Check className="h-4 w-4" /> Added
        </>
      ) : (
        <>
          <ShoppingCart className="h-4 w-4" /> {outOfStock ? "Out of Stock" : label}
        </>
      )}
    </button>
  );
}
