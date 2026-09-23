"use client";

import Link from "next/link";
import { useCallback, useEffect, useRef, useState } from "react";
import {
  ChevronDown,
  CircleUser,
  Heart,
  LayoutDashboard,
  MapPin,
  Package,
  Shield,
  ShoppingCart,
  User,
} from "lucide-react";
import { logout } from "@/app/actions/auth";
import { useCart } from "@/components/providers/cart";
import { useSession } from "@/components/providers/session";
import { useDismiss } from "./use-dismiss";

export function AccountMenu({ adminPanelUrl }: { adminPanelUrl: string }) {
  const { user, wishlistCount } = useSession();
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);
  const close = useCallback(() => setOpen(false), []);
  useDismiss(ref, open, close);

  if (!user) {
    return (
      <Link href="/login" className="text-gray-700 hover:text-blue-600" aria-label="Log in">
        <User className="h-5 w-5" />
      </Link>
    );
  }

  const item = "flex items-center gap-2 px-4 py-2 hover:bg-gray-50";

  return (
    <div className="relative" ref={ref}>
      <button
        type="button"
        onClick={() => setOpen((o) => !o)}
        aria-haspopup="true"
        aria-expanded={open}
        className="flex max-w-[9rem] items-center gap-1 text-sm font-medium text-gray-700 hover:text-blue-600"
      >
        <span className="truncate">{user.name}</span> <ChevronDown className="h-3.5 w-3.5 shrink-0" />
      </button>
      {open && (
        <div className="absolute right-0 top-full z-20 w-48 pt-2">
          {/* Links close the menu; the logout form must stay mounted until it submits. */}
          <div className="rounded-lg border bg-white py-1 text-sm shadow-lg">
            {user.is_admin ? (
              adminPanelUrl && (
                <a href={adminPanelUrl} className={item} onClick={close}>
                  <LayoutDashboard className="h-4 w-4" /> Admin Panel
                </a>
              )
            ) : (
              <>
                <Link href="/account" className={item} onClick={close}>
                  <CircleUser className="h-4 w-4" /> My Account
                </Link>
                <Link href="/account/orders" className={item} onClick={close}>
                  <Package className="h-4 w-4" /> My Orders
                </Link>
                <Link href="/account/wishlist" className={item} onClick={close}>
                  <Heart className="h-4 w-4" /> Wishlist
                  {wishlistCount > 0 && <span className="ml-auto text-xs text-gray-400">{wishlistCount}</span>}
                </Link>
                <Link href="/account/addresses" className={item} onClick={close}>
                  <MapPin className="h-4 w-4" /> My Addresses
                </Link>
                <Link href="/account/profile" className={item} onClick={close}>
                  <Shield className="h-4 w-4" /> Privacy Settings
                </Link>
              </>
            )}
            <form action={logout}>
              <button className="w-full px-4 py-2 text-left hover:bg-gray-50">Logout</button>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

export function WishlistLink() {
  const { user, wishlistCount } = useSession();
  if (!user || user.is_admin) return null;

  return (
    <Link href="/account/wishlist" className="relative text-gray-700 hover:text-red-500" aria-label="Wishlist">
      <Heart className="h-6 w-6" />
      {wishlistCount > 0 && (
        <span className="absolute -right-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] text-white">
          {wishlistCount}
        </span>
      )}
    </Link>
  );
}

export function CartLink() {
  const { count, hydrated } = useCart();
  const [bump, setBump] = useState(false);
  const previous = useRef(count);

  useEffect(() => {
    if (hydrated && count > previous.current) {
      // Replay the bump animation whenever something is added.
      setBump(true);
      const t = window.setTimeout(() => setBump(false), 400);
      previous.current = count;
      return () => window.clearTimeout(t);
    }
    previous.current = count;
  }, [count, hydrated]);

  return (
    <Link href="/cart" className="relative text-gray-700 hover:text-blue-600" aria-label={`Cart (${count} items)`}>
      <ShoppingCart className="h-6 w-6" />
      {hydrated && count > 0 && (
        <span
          className={`absolute -right-2 -top-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-blue-600 px-0.5 text-[10px] text-white ${bump ? "cart-bump" : ""}`}
        >
          {count}
        </span>
      )}
    </Link>
  );
}
