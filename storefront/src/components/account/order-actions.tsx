"use client";

import { useRouter } from "next/navigation";
import { useState, useTransition } from "react";
import { CircleX, Undo2 } from "lucide-react";
import { cancelOrder, requestReturn } from "@/app/actions/account";
import { useToast } from "@/components/providers/toast";
import { formatDate } from "@/lib/format";
import type { Order } from "@/lib/types";

export function OrderActions({ order }: { order: Order }) {
  const router = useRouter();
  const toast = useToast();
  const [open, setOpen] = useState(false);
  const [reason, setReason] = useState("");
  const [pending, start] = useTransition();

  const run = (action: () => ReturnType<typeof cancelOrder>) =>
    start(async () => {
      const res = await action();
      toast(res.ok ? (res.message ?? "Done.") : res.message, res.ok ? "success" : "error", 6000);
      if (res.ok) {
        setOpen(false);
        router.refresh();
      }
    });

  if (order.can_cancel) {
    return (
      <div className="mt-4">
        <button
          type="button"
          onClick={() => setOpen((o) => !o)}
          className="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-sm text-red-600 hover:bg-red-50"
        >
          <CircleX className="h-4 w-4" /> Cancel Order
        </button>
        {open && (
          <form
            className="mt-3 rounded-lg border border-red-100 bg-red-50 p-4"
            onSubmit={(e) => {
              e.preventDefault();
              if (window.confirm("Cancel this order? This can't be undone.")) run(() => cancelOrder(order.order_number, reason));
            }}
          >
            <label className="text-sm font-medium">
              Why are you cancelling? <span className="font-normal text-gray-400">— optional</span>
              <textarea value={reason} onChange={(e) => setReason(e.target.value)} rows={2} maxLength={500} className="mt-1 w-full rounded-lg border px-3 py-2 text-sm" />
            </label>
            <p className="mt-1 text-xs text-gray-500">Orders can only be cancelled before they ship. If you paid by card, you&apos;ll be refunded automatically.</p>
            <button disabled={pending} className="mt-3 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-70">
              {pending ? "Cancelling…" : "Confirm Cancellation"}
            </button>
          </form>
        )}
      </div>
    );
  }

  if (order.can_request_return) {
    return (
      <div className="mt-4">
        <button
          type="button"
          onClick={() => setOpen((o) => !o)}
          className="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50"
        >
          <Undo2 className="h-4 w-4" /> Request Return
        </button>
        {order.return_window_expires_at && (
          <p className="mt-1.5 text-xs text-gray-400">Eligible until {formatDate(order.return_window_expires_at)} (7 days from delivery).</p>
        )}
        {open && (
          <form
            className="mt-3 rounded-lg border border-blue-100 bg-blue-50 p-4"
            onSubmit={(e) => {
              e.preventDefault();
              run(() => requestReturn(order.order_number, reason));
            }}
          >
            <label className="text-sm font-medium">
              Reason for return
              <textarea value={reason} onChange={(e) => setReason(e.target.value)} rows={3} required maxLength={1000} className="mt-1 w-full rounded-lg border px-3 py-2 text-sm" />
            </label>
            <button disabled={pending} className="mt-3 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-70">
              {pending ? "Submitting…" : "Submit Return Request"}
            </button>
          </form>
        )}
      </div>
    );
  }

  if (order.return_status === "requested") {
    return <p className="mt-4 text-sm text-gray-500">Your return request is being reviewed. We&apos;ll update you here once it&apos;s decided.</p>;
  }

  if (order.status === "delivered" && !order.return_status) {
    return <p className="mt-4 text-xs text-gray-400">The 7-day return window for this order has closed.</p>;
  }

  return null;
}
