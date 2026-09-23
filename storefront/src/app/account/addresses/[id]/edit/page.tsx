import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { AddressForm } from "@/components/account/address-form";
import { api } from "@/lib/api";
import type { Address } from "@/lib/types";

export const metadata: Metadata = { title: "Edit Address" };

export default async function EditAddressPage({ params }: PageProps<"/account/addresses/[id]/edit">) {
  const { id } = await params;
  if (!/^\d+$/.test(id)) notFound();
  const res = await api<{ data: Address }>(`/addresses/${id}`);
  if (!res.ok) {
    if (res.status === 404) notFound();
    throw new Error(res.message);
  }

  return (
    <div className="rounded-xl bg-white p-6 shadow-sm">
      <h2 className="mb-5 text-xl font-bold">Edit Address</h2>
      <AddressForm address={res.data.data} defaultName="" />
    </div>
  );
}
