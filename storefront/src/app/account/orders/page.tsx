import type { Metadata } from "next";
import Link from "next/link";
import { ChevronRight, Package, PackageSearch, Receipt } from "lucide-react";
import { Pagination } from "@/components/ui/pagination";
import { api } from "@/lib/api";
import { capitalize, formatDate, money, paymentStatusColor, plural, returnStatusColor, statusColor } from "@/lib/format";
import type { Order, Paginated } from "@/lib/types";

export const metadata: Metadata = { title: "My Orders" };

export default async function OrdersPage({ searchParams }: PageProps<"/account/orders">) {
  const page = Number((await searchParams).page) || 1;
  const res = await api<Paginated<Order>>(`/orders?page=${page}`);
  if (!res.ok) throw new Error(res.message);
  const { data: orders, meta } = res.data;

  return (
    <>
      <div className="mb-6 flex items-center gap-3">
        <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600"><Package className="h-6 w-6" /></div>
        <div>
          <h2 className="text-2xl font-bold text-slate-900">My Orders</h2>
          <p className="text-sm text-gray-500">{meta.total} {plural("order", meta.total)} placed</p>
        </div>
      </div>

      {orders.length === 0 ? (
        <div className="rounded-2xl border border-gray-100 bg-white p-12 text-center shadow-sm">
          <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-500"><PackageSearch className="h-7 w-7" /></div>
          <h3 className="mb-1 font-semibold text-slate-900">No orders yet</h3>
          <p className="mb-5 text-sm text-gray-500">When you place an order, it will show up here.</p>
          <Link href="/shop" className="inline-block rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-700">Start shopping</Link>
        </div>
      ) : (
        <>
          <div className="divide-y divide-gray-100 rounded-2xl border border-gray-100 bg-white shadow-sm">
            {orders.map((order) => (
              <Link key={order.order_number} href={`/account/orders/${order.order_number}`} className="flex flex-wrap items-center gap-4 p-5 hover:bg-gray-50">
                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><Receipt className="h-5 w-5" /></div>
                <div className="min-w-[10rem] flex-1">
                  <div className="font-semibold text-slate-900">{order.order_number}</div>
                  <div className="text-xs text-gray-500">
                    {formatDate(order.created_at, true)} &middot; {order.items_count} {plural("item", order.items_count ?? 0)}
                  </div>
                </div>
                <span className={`rounded-full px-2.5 py-1 text-xs font-medium ${statusColor(order.status)}`}>{capitalize(order.status)}</span>
                {order.return_status && (
                  <span className={`rounded-full px-2.5 py-1 text-xs font-medium ${returnStatusColor(order.return_status)}`}>{order.return_status_label}</span>
                )}
                <span className={`rounded-full px-2.5 py-1 text-xs font-medium ${paymentStatusColor(order.payment_status)}`}>{order.payment_status_label}</span>
                <div className="w-20 text-right font-bold text-slate-900">{money(order.total)}</div>
                <ChevronRight className="h-4 w-4 text-gray-300" />
              </Link>
            ))}
          </div>
          <div className="mt-6"><Pagination meta={meta} basePath="/account/orders" /></div>
        </>
      )}
    </>
  );
}
