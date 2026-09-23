import type { Metadata } from "next";
import Link from "next/link";
import { MapPin } from "lucide-react";
import { AddressCardActions } from "@/components/account/address-actions";
import { api } from "@/lib/api";
import type { Address } from "@/lib/types";

export const metadata: Metadata = { title: "My Addresses" };

export default async function AddressesPage() {
  const res = await api<{ data: Address[] }>("/addresses");
  if (!res.ok) throw new Error(res.message);
  const addresses = res.data.data;

  return (
    <>
      <div className="mb-5 flex items-center justify-between">
        <h2 className="flex items-center gap-2 font-bold text-slate-900"><MapPin className="h-5 w-5 text-blue-600" /> My Addresses</h2>
        <Link href="/account/addresses/new" className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white">+ Add New Address</Link>
      </div>
      {addresses.length === 0 ? (
        <div className="rounded-xl bg-white p-12 text-center shadow-sm">
          <p className="mb-4 text-gray-400">You haven&apos;t saved any addresses yet.</p>
          <Link href="/account/addresses/new" className="rounded-lg bg-blue-600 px-5 py-2 text-sm text-white">Add an Address</Link>
        </div>
      ) : (
        <div className="grid gap-4 sm:grid-cols-2">
          {addresses.map((a) => (
            <div key={a.id} className="relative rounded-xl bg-white p-5 shadow-sm">
              {a.is_default && (
                <span className="absolute right-4 top-4 rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700">Default</span>
              )}
              <div className="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-400">{a.label}</div>
              <div className="font-semibold">{a.full_name}</div>
              <p className="mt-1 text-sm text-gray-600">
                {a.line1}{a.line2 ? `, ${a.line2}` : ""}
                <br />
                {a.city}, {a.state} {a.postal_code}
                <br />
                {a.country}
              </p>
              <p className="mt-1 text-sm text-gray-500">Phone: {a.phone}</p>
              <AddressCardActions address={a} />
            </div>
          ))}
        </div>
      )}
    </>
  );
}
