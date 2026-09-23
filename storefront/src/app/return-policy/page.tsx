import type { Metadata } from "next";
import Link from "next/link";
import type { ReactNode } from "react";

export const metadata: Metadata = { title: "Return Policy" };

export default function ReturnPolicyPage() {
  const steps: [string, ReactNode][] = [
    // Matches what the store enforces: return requests are accepted for 7 days after delivery.
    ["Return Window", "You can request a return within 7 days of delivery for a refund."],
    ["Condition", "Items must be unused, in original packaging, and with tags attached."],
    [
      "How to Initiate a Return",
      <>
        Go to <Link href="/account/orders" className="text-blue-600 hover:underline">My Orders</Link>, select the
        order, and choose &quot;Request Return&quot; — or{" "}
        <Link href="/contact" className="text-blue-600 hover:underline">contact our support team</Link> with your
        order number.
      </>,
    ],
    ["Refunds", "Once we receive and inspect your return, refunds are processed within 5–7 business days to your original payment method."],
  ];

  return (
    <div className="mx-auto max-w-4xl px-4 py-12">
      <h1 className="mb-2 text-3xl font-bold">Return Policy</h1>
      <p className="mb-8 text-gray-500">Hassle-Free Returns Policy</p>
      <div className="space-y-6 rounded-xl bg-white p-6 shadow-sm sm:p-8">
        {steps.map(([title, body], i) => (
          <div key={title} className="flex gap-4">
            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 font-semibold text-blue-600">{i + 1}</div>
            <div>
              <h2 className="mb-1 font-semibold">{title}</h2>
              <p className="text-sm leading-relaxed text-gray-600">{body}</p>
            </div>
          </div>
        ))}
        <p className="border-t pt-4 text-xs text-gray-400">
          Some items (e.g. perishables, personal care, custom orders) may not be eligible for return — check the product page for details.
        </p>
      </div>
    </div>
  );
}
