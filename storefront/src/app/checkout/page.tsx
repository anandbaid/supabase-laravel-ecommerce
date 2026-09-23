import type { Metadata } from "next";
import Link from "next/link";
import { redirect } from "next/navigation";
import { CheckoutForm } from "@/components/checkout/checkout-form";
import { api } from "@/lib/api";
import { getMe, getSettings } from "@/lib/data";
import type { Address } from "@/lib/types";

export const metadata: Metadata = { title: "Checkout" };

export default async function CheckoutPage({ searchParams }: PageProps<"/checkout">) {
  const me = await getMe();
  if (!me) redirect("/login?next=/checkout");

  if (me.user.is_admin) {
    return (
      <div className="mx-auto max-w-xl px-4 py-16 text-center">
        <h1 className="mb-2 text-2xl font-bold">Checkout</h1>
        <p className="mb-6 text-gray-600">Admin accounts cannot place orders. Please use a customer account to checkout.</p>
        <Link href="/cart" className="font-medium text-blue-600 hover:underline">Back to cart</Link>
      </div>
    );
  }

  const [res, settings, sp] = await Promise.all([
    api<{ addresses: { data?: Address[] } | Address[]; prefill: { data?: Address } | Address | null }>("/checkout"),
    getSettings(),
    searchParams,
  ]);

  const addresses = res.ok ? unwrapList(res.data.addresses) : [];
  const prefill = res.ok ? unwrapOne(res.data.prefill) : null;

  return (
    <CheckoutForm
      user={me.user}
      addresses={addresses}
      prefill={prefill}
      freeShippingThreshold={settings.free_shipping_threshold}
      paymentCancelled={sp.payment === "cancelled"}
    />
  );
}

// Laravel resources nested in a plain JSON response may or may not carry a `data` wrapper.
function unwrapList(v: { data?: Address[] } | Address[]): Address[] {
  return Array.isArray(v) ? v : (v.data ?? []);
}
function unwrapOne(v: { data?: Address } | Address | null): Address | null {
  if (!v) return null;
  return "id" in v ? (v as Address) : ((v as { data?: Address }).data ?? null);
}
