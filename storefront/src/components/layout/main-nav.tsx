"use client";

import Link from "next/link";
import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { Suspense, useState, type ReactNode } from "react";
import { Menu, X } from "lucide-react";
import type { Category } from "@/lib/types";

function useActive() {
  const pathname = usePathname();
  const params = useSearchParams();
  const deals = params.get("deals");
  return {
    home: pathname === "/",
    shop: pathname.startsWith("/shop") && !deals,
    deals: pathname.startsWith("/shop") && Boolean(deals),
    about: pathname === "/about",
    contact: pathname === "/contact",
  };
}

function NavLinks({ categoriesSlot }: { categoriesSlot: ReactNode }) {
  const active = useActive();
  const cls = (on: boolean) => `${on ? "text-blue-600" : "text-gray-700"} hover:text-blue-600`;
  return (
    <>
      <Link href="/" className={cls(active.home)}>Home</Link>
      <Link href="/shop" className={cls(active.shop)}>Shop</Link>
      {categoriesSlot}
      <Link href="/shop?deals=1" className={cls(active.deals)}>Deals</Link>
      <Link href="/about" className={cls(active.about)}>About</Link>
      <Link href="/contact" className={cls(active.contact)}>Contact</Link>
    </>
  );
}

export function MainNav({ children }: { children: ReactNode }) {
  return (
    <nav className="hidden items-center gap-6 text-sm font-medium md:flex">
      <Suspense fallback={null}>
        <NavLinks categoriesSlot={children} />
      </Suspense>
    </nav>
  );
}

/** Slide-down menu for small screens (the desktop nav is hidden below md). */
export function MobileNav({ categories }: { categories: Category[] }) {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const close = () => setOpen(false);

  return (
    <div className="md:hidden">
      <button
        type="button"
        onClick={() => setOpen((o) => !o)}
        aria-expanded={open}
        aria-label={open ? "Close menu" : "Open menu"}
        className="-ml-1 p-1 text-gray-700"
      >
        {open ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
      </button>
      {open && (
        <div className="absolute inset-x-0 top-full border-t bg-white shadow-lg">
          <form
            className="px-4 pt-4"
            onSubmit={(e) => {
              e.preventDefault();
              const q = String(new FormData(e.currentTarget).get("search") ?? "").trim();
              close();
              router.push(q ? `/shop?search=${encodeURIComponent(q)}` : "/shop");
            }}
          >
            <input
              type="search"
              name="search"
              placeholder="Search for products..."
              className="w-full rounded-full border border-gray-200 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            />
          </form>
          <nav className="grid gap-1 p-4 text-sm font-medium" onClick={close}>
            <Link href="/" className="rounded-lg px-3 py-2 hover:bg-gray-50">Home</Link>
            <Link href="/shop" className="rounded-lg px-3 py-2 hover:bg-gray-50">Shop</Link>
            <Link href="/shop?deals=1" className="rounded-lg px-3 py-2 hover:bg-gray-50">Deals</Link>
            {categories.map((c) => (
              <Link key={c.id} href={`/shop?category=${c.slug}`} className="rounded-lg px-3 py-2 text-gray-500 hover:bg-gray-50">
                {c.name}
              </Link>
            ))}
            <Link href="/about" className="rounded-lg px-3 py-2 hover:bg-gray-50">About</Link>
            <Link href="/contact" className="rounded-lg px-3 py-2 hover:bg-gray-50">Contact</Link>
          </nav>
        </div>
      )}
    </div>
  );
}
