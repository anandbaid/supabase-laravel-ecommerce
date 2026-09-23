"use client";

import { useState } from "react";
import { Heart } from "lucide-react";
import { useSession } from "@/components/providers/session";

export function WishlistButton({ productId, variant = "icon" }: { productId: number; variant?: "icon" | "full" }) {
  const { user, wishlistIds, toggleWishlist, pending } = useSession();
  const [pop, setPop] = useState(0);

  if (user?.is_admin) return null;

  const saved = wishlistIds.has(productId);
  const busy = pending.has(productId);
  const onClick = async () => {
    await toggleWishlist(productId);
    setPop((n) => n + 1);
  };
  const icon = (
    <Heart key={pop} className={`h-4 w-4 ${pop ? "wishlist-pop" : ""}`} fill={saved ? "currentColor" : "none"} />
  );

  if (variant === "full") {
    return (
      <button
        type="button"
        onClick={onClick}
        disabled={busy}
        aria-pressed={saved}
        className={`mt-3 flex w-full items-center justify-center gap-2 rounded-lg border px-5 py-2.5 text-sm transition ${
          saved ? "border-red-200 text-red-500" : "border-gray-200 text-gray-600 hover:bg-gray-50"
        }`}
      >
        {icon} {saved ? "Saved to Wishlist" : "Add to Wishlist"}
      </button>
    );
  }

  return (
    <button
      type="button"
      onClick={onClick}
      disabled={busy}
      aria-pressed={saved}
      title={saved ? "Remove from wishlist" : "Save to wishlist"}
      aria-label={saved ? "Remove from wishlist" : "Save to wishlist"}
      className={`absolute right-3 top-3 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 shadow transition ${
        saved ? "text-red-500" : "text-gray-400 hover:text-red-500"
      }`}
    >
      {icon}
    </button>
  );
}
