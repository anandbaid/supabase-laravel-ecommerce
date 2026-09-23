"use client";

import { createContext, useCallback, useContext, useMemo, useState, type ReactNode } from "react";
import { usePathname, useRouter } from "next/navigation";
import { toggleWishlist } from "@/app/actions/shop";
import type { User } from "@/lib/types";
import { useToast } from "./toast";

type SessionValue = {
  user: User | null;
  wishlistIds: Set<number>;
  wishlistCount: number;
  toggleWishlist: (productId: number) => Promise<void>;
  pending: Set<number>;
};

const SessionContext = createContext<SessionValue | null>(null);

export function useSession() {
  const ctx = useContext(SessionContext);
  if (!ctx) throw new Error("useSession must be used inside <SessionProvider>");
  return ctx;
}

/** The signed-in customer and their wishlist, seeded by the root layout. */
export function SessionProvider({
  user,
  wishlistIds,
  children,
}: {
  user: User | null;
  wishlistIds: number[];
  children: ReactNode;
}) {
  const router = useRouter();
  const pathname = usePathname();
  const toast = useToast();
  const [ids, setIds] = useState(() => new Set(wishlistIds));
  const [pending, setPending] = useState(() => new Set<number>());

  // Re-seed when the server sends a different wishlist (e.g. after logging in).
  const serverKey = wishlistIds.join(",");
  const [seenKey, setSeenKey] = useState(serverKey);
  if (serverKey !== seenKey) {
    setSeenKey(serverKey);
    setIds(new Set(wishlistIds));
  }

  const toggle = useCallback(
    async (productId: number) => {
      if (!user) {
        router.push(`/login?next=${encodeURIComponent(pathname)}`);
        return;
      }
      setPending((p) => new Set(p).add(productId));
      const res = await toggleWishlist(productId);
      setPending((p) => {
        const next = new Set(p);
        next.delete(productId);
        return next;
      });
      if (!res.ok) {
        if (res.status === 401) router.push(`/login?next=${encodeURIComponent(pathname)}`);
        else toast(res.message, "error");
        return;
      }
      setIds((current) => {
        const next = new Set(current);
        if (res.data.wishlisted) next.add(productId);
        else next.delete(productId);
        return next;
      });
      toast(res.message ?? "", res.data.wishlisted ? "success" : "info", 3000);
      // The wishlist page lists the saved products, so it needs re-rendering.
      if (pathname.startsWith("/account")) router.refresh();
    },
    [user, router, pathname, toast],
  );

  const value = useMemo(
    () => ({ user, wishlistIds: ids, wishlistCount: ids.size, toggleWishlist: toggle, pending }),
    [user, ids, toggle, pending],
  );

  return <SessionContext.Provider value={value}>{children}</SessionContext.Provider>;
}
