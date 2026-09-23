import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { ArrowLeft, Check } from "lucide-react";
import { OrderActions } from "@/components/account/order-actions";
import { OrderTotals } from "@/components/account/order-totals";
import { api } from "@/lib/api";
import { capitalize, formatDate, paymentStatusColor, returnStatusColor, statusColor } from "@/lib/format";
import type { Order } from "@/lib/types";

export async function generateMetadata({ params }: PageProps<"/account/orders/[orderNumber]">): Promise<Metadata> {
  return { title: `Order ${(await params).orderNumber}` };
}

const STEPS = [
  ["pending", "Placed"],
  ["processing", "Processing"],
  ["shipped", "Shipped"],
  ["delivered", "Delivered"],
] as const;

export default async function OrderPage({ params }: PageProps<"/account/orders/[orderNumber]">) {
  const { orderNumber } = await params;
  const res = await api<{ data: Order }>(`/orders/${encodeURIComponent(orderNumber)}`);
  if (!res.ok) {
    if (res.status === 404) notFound();
    throw new Error(res.message);
  }
  const order = res.data.data;
  const current = Math.max(0, STEPS.findIndex(([key]) => key === order.status));

  return (
    <>
      <Link href="/account/orders" className="mb-4 inline-flex items-center gap-2 text-sm text-blue-600 hover:underline">
        <ArrowLeft className="h-4 w-4" /> Back to My Orders
      </Link>
      <div className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div className="mb-2 flex flex-wrap items-start justify-between gap-3">
          <div>
            <h2 className="text-xl font-bold text-slate-900">Order {order.order_number}</h2>
            <p className="text-sm text-gray-500">Placed {formatDate(order.created_at, true)}</p>
          </div>
          <div className="flex flex-wrap items-center justify-end gap-2">
            <span className={`rounded-full px-2.5 py-1 text-xs font-medium ${statusColor(order.status)}`}>{capitalize(order.status)}</span>
            <span className={`rounded-full px-2.5 py-1 text-xs font-medium ${paymentStatusColor(order.payment_status)}`}>{order.payment_status_label}</span>
            {order.return_status && (
              <span className={`rounded-full px-2.5 py-1 text-xs font-medium ${returnStatusColor(order.return_status)}`}>{order.return_status_label}</span>
            )}
          </div>
        </div>

        {order.status !== "cancelled" && (
          <ol className="mb-2 mt-6 flex items-center" aria-label="Order progress">
            {STEPS.map(([key, label], i) => {
              const done = i <= current;
              const last = i === STEPS.length - 1;
              return (
                <li key={key} className={`flex items-center ${last ? "flex-none" : "flex-1"}`}>
                  <div className="flex flex-col items-center">
                    <div className={`flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold ${done ? "bg-blue-600 text-white" : "bg-gray-100 text-gray-400"}`}>
                      {done ? <Check className="h-3.5 w-3.5" /> : i + 1}
                    </div>
                    <span className={`mt-1 text-[11px] ${done ? "font-medium text-blue-600" : "text-gray-400"}`}>{label}</span>
                  </div>
                  {!last && <div className={`-mt-4 h-0.5 flex-1 ${i < current ? "bg-blue-600" : "bg-gray-100"}`} />}
                </li>
              );
            })}
          </ol>
        )}

        {order.status === "cancelled" && order.cancelled_at && (
          <div className="mt-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">
            Cancelled on {formatDate(order.cancelled_at)}
            {order.cancellation_reason ? ` — "${order.cancellation_reason}"` : ""}
          </div>
        )}

        <OrderActions order={order} />

        <div className="mt-4 border-t border-gray-100 pt-4">
          <h3 className="mb-3 font-semibold text-slate-900">Items</h3>
          <OrderTotals order={order} />
        </div>

        <div className="mt-4 grid gap-4 border-t border-gray-100 pt-4 text-sm sm:grid-cols-2">
          <div>
            <h3 className="mb-1 font-semibold text-slate-900">Shipping address</h3>
            <p className="whitespace-pre-line text-gray-600">{order.shipping_address}</p>
          </div>
          <div>
            <h3 className="mb-1 font-semibold text-slate-900">Payment method</h3>
            <p className="text-gray-600">{order.payment_method === "cod" ? "Cash on Delivery" : "Card (Stripe)"}</p>
            {order.notes && (
              <>
                <h3 className="mb-1 mt-3 font-semibold text-slate-900">Order notes</h3>
                <p className="whitespace-pre-line text-gray-600">{order.notes}</p>
              </>
            )}
          </div>
        </div>
      </div>
    </>
  );
}
