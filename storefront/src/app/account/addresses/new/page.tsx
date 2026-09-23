import type { Metadata } from "next";
import { AddressForm } from "@/components/account/address-form";
import { getMe } from "@/lib/data";

export const metadata: Metadata = { title: "Add Address" };

export default async function NewAddressPage() {
  const me = await getMe();
  return (
    <div className="rounded-xl bg-white p-6 shadow-sm">
      <h2 className="mb-5 text-xl font-bold">Add New Address</h2>
      <AddressForm address={null} defaultName={me?.user.name ?? ""} />
    </div>
  );
}
