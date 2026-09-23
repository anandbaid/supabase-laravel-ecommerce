import type { Metadata } from "next";
import Link from "next/link";
import { Package, PackageSearch, User } from "lucide-react";
import { api } from "@/lib/api";
import { capitalize, money, statusColor } from "@/lib/format";
import type { Order, User as UserType } from "@/lib/types";

export const metadata: Metadata = { title: "My Account" };

type Dashboard = {
  user: UserType;
  orders_count: number;
  wishlist_count: number;
  loyalty_points: number;
  recent_orders: Order[];
};

export default async function AccountDashboard() {
  const res = await api<Dashboard>("/account/dashboard");
  if (!res.ok) throw new Error(res.message);
  const d = res.data;
  const since = d.user.created_at
    ? new Date(d.user.created_at).toLocaleDateString("en-US", { month: "short", year: "numeric" })
    : null;

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4 rounded-xl bg-white p-6 shadow-sm">
        <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600">
          <User className="h-8 w-8" />
        </div>
        <div>
          <div className="text-lg font-bold text-slate-900">{d.user.name}</div>
          <div className="text-sm text-gray-500">{d.user.email}</div>
          {since && <div className="mt-0.5 text-xs text-gray-400">Member since {since}</div>}
        </div>
      </div>

      <div className="grid grid-cols-3 gap-4">
        {[
          ["Total Orders", d.orders_count],
          ["Wishlist Items", d.wishlist_count],
          ["Loyalty Points", d.loyalty_points],
        ].map(([label, value]) => (
          <div key={label} className="rounded-xl bg-white p-5 text-center shadow-sm">
            <div className="mb-1 text-xs text-gray-400">{label}</div>
            <div className="text-2xl font-bold text-slate-900">{value}</div>
          </div>
        ))}
      </div>

      <div className="rounded-xl bg-white p-6 shadow-sm">
        <div className="mb-4 flex items-center justify-between">
          <h2 className="font-bold text-slate-900">Recent Orders</h2>
          <Link href="/account/orders" className="text-sm text-blue-600 hover:underline">View All</Link>
        </div>
        {d.recent_orders.length === 0 ? (
          <div className="py-10 text-center">
            <div className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-blue-500">
              <PackageSearch className="h-6 w-6" />
            </div>
            <p className="mb-4 text-sm text-gray-500">No orders yet.</p>
            <Link href="/shop" className="inline-block rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Start shopping</Link>
          </div>
        ) : (
          <div className="divide-y">
            {d.recent_orders.map((order) => {
              const first = order.items?.[0];
              return (
                <Link
                  key={order.order_number}
                  href={`/account/orders/${order.order_number}`}
                  className="-mx-2 flex items-center gap-4 rounded-lg px-2 py-4 hover:bg-gray-50"
                >
                  <div className="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-gray-50">
                    {first?.image ? (
                      // eslint-disable-next-line @next/next/no-img-element
                      <img src={first.image} alt="" className="h-full w-full object-contain" />
                    ) : (
                      <Package className="h-5 w-5 text-gray-400" />
                    )}
                  </div>
                  <div className="min-w-0 flex-1">
                    <div className="truncate font-medium text-slate-900">{first?.product_name ?? `Order ${order.order_number}`}</div>
                    <div className="text-xs text-gray-400">Order #{order.order_number}</div>
                  </div>
                  <span className={`rounded-full px-2.5 py-1 text-xs font-medium ${statusColor(order.status)}`}>{capitalize(order.status)}</span>
                  <div className="hidden w-24 text-right text-xs text-gray-400 sm:block">
                    {new Date(order.created_at).toLocaleDateString("en-US", { month: "short", day: "2-digit", year: "numeric" })}
                  </div>
                  <div className="w-20 text-right font-bold text-slate-900">{money(order.total)}</div>
                </Link>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
}
