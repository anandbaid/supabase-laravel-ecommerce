import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { CircleCheck } from "lucide-react";
import { OrderTotals } from "@/components/account/order-totals";
import { PaymentPending } from "@/components/checkout/payment-pending";
import { Alert } from "@/components/ui/form";
import { api } from "@/lib/api";
import { capitalize, paymentStatusColor, statusColor } from "@/lib/format";
import type { Order } from "@/lib/types";

export const metadata: Metadata = { title: "Order Confirmed" };

export default async function OrderSuccessPage({ params }: PageProps<"/checkout/success/[orderNumber]">) {
  const { orderNumber } = await params;
  const res = await api<{ data: Order }>(`/orders/${encodeURIComponent(orderNumber)}`);
  if (!res.ok) {
    if (res.status === 404) notFound();
    throw new Error(res.message);
  }
  const order = res.data.data;
  const awaitingCard = order.payment_method === "card" && order.payment_status === "unpaid";

  return (
    <div className="mx-auto max-w-2xl px-4 py-16 text-center">
      <CircleCheck className="mx-auto mb-4 h-14 w-14 text-green-500" />
      <h1 className="mb-2 text-2xl font-bold">Thank you, {order.customer_name}!</h1>
      <p className="mb-6 text-gray-500">
        Your order <span className="font-semibold text-blue-600">#{order.order_number}</span> has been placed successfully.
      </p>
      <div className="mb-6 flex items-center justify-center gap-3 text-sm">
        <span className={`rounded-full px-3 py-1 ${statusColor(order.status)}`}>{capitalize(order.status)}</span>
        <span className={`rounded-full px-3 py-1 ${paymentStatusColor(order.payment_status)}`}>{order.payment_status_label}</span>
      </div>

      {awaitingCard && (
        <div className="mb-6 text-left">
          <Alert tone="info">
            We&apos;re still confirming your payment with Stripe. This page will update automatically once it&apos;s confirmed.
          </Alert>
          <PaymentPending />
        </div>
      )}
      {order.payment_method === "card" && order.payment_status === "failed" && (
        <div className="mb-6 text-left">
          <Alert tone="error">
            Your payment didn&apos;t go through. Your order has been recorded, but nothing has been charged — please contact
            us or try checking out again.
          </Alert>
        </div>
      )}

      <div className="rounded-xl bg-white p-6 text-left shadow-sm">
        <h2 className="mb-3 font-semibold">Order Items</h2>
        <OrderTotals order={order} />
      </div>

      <div className="mt-8 flex flex-wrap justify-center gap-3">
        <Link href="/shop" className="inline-block rounded-lg bg-blue-600 px-6 py-3 font-medium text-white">Continue Shopping</Link>
        <Link href={`/account/orders/${order.order_number}`} className="inline-block rounded-lg border border-gray-200 bg-white px-6 py-3 font-medium text-gray-700">
          View order
        </Link>
      </div>
    </div>
  );
}
