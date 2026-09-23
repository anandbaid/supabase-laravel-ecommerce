"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { Heart, LayoutDashboard, LogOut, MapPin, Package, User } from "lucide-react";
import { logout } from "@/app/actions/auth";

const ITEMS = [
  { href: "/account", label: "Dashboard", Icon: LayoutDashboard, exact: true },
  { href: "/account/orders", label: "Orders", Icon: Package },
  { href: "/account/wishlist", label: "Wishlist", Icon: Heart },
  { href: "/account/addresses", label: "Addresses", Icon: MapPin },
  { href: "/account/profile", label: "Account Details", Icon: User },
];

export function AccountSidebar() {
  const pathname = usePathname();

  return (
    <div className="h-fit rounded-xl bg-white p-3 shadow-sm">
      <nav className="space-y-1 text-sm">
        {ITEMS.map(({ href, label, Icon, exact }) => {
          const active = exact ? pathname === href : pathname.startsWith(href);
          return (
            <Link
              key={href}
              href={href}
              className={`flex items-center gap-2.5 rounded-lg px-3 py-2.5 ${active ? "bg-blue-50 font-medium text-blue-600" : "text-gray-600 hover:bg-gray-50"}`}
            >
              <Icon className="h-4 w-4" /> {label}
            </Link>
          );
        })}
        <form action={logout}>
          <button className="flex w-full items-center gap-2.5 rounded-lg px-3 py-2.5 text-left text-gray-600 hover:bg-gray-50">
            <LogOut className="h-4 w-4" /> Logout
          </button>
        </form>
      </nav>
    </div>
  );
}
