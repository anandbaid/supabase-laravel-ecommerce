"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useState, useTransition } from "react";
import { saveAddress } from "@/app/actions/account";
import { useToast } from "@/components/providers/toast";
import { Alert, Field, inputClass } from "@/components/ui/form";
import type { Address } from "@/lib/types";

export function AddressForm({ address, defaultName }: { address: Address | null; defaultName: string }) {
  const router = useRouter();
  const toast = useToast();
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [message, setMessage] = useState<string | null>(null);
  const [pending, start] = useTransition();

  return (
    <form
      className="space-y-4"
      onSubmit={(e) => {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.currentTarget));
        start(async () => {
          const res = await saveAddress(address?.id ?? null, { ...data, is_default: data.is_default === "1" });
          if (!res.ok) {
            setErrors(res.errors ?? {});
            setMessage(res.errors ? null : res.message);
            return;
          }
          toast(res.message ?? "Saved.", "success");
          router.push("/account/addresses");
          router.refresh();
        });
      }}
    >
      {message && <Alert tone="error">{message}</Alert>}
      <label className="block text-sm font-medium">
        Address Label
        <select name="label" defaultValue={address?.label ?? "Home"} className={`${inputClass} mt-1`}>
          {["Home", "Work", "Other", ...(address && !["Home", "Work", "Other"].includes(address.label) ? [address.label] : [])].map((l) => (
            <option key={l} value={l}>{l}</option>
          ))}
        </select>
      </label>
      <div className="grid grid-cols-2 gap-4">
        <Field name="full_name" label="Full Name" defaultValue={address?.full_name ?? defaultName} required errors={errors} />
        <Field name="phone" label="Phone" defaultValue={address?.phone ?? ""} required errors={errors} />
      </div>
      <Field name="line1" label="Address Line 1" defaultValue={address?.line1 ?? ""} required errors={errors} />
      <Field name="line2" label="Address Line 2" defaultValue={address?.line2 ?? ""} optional errors={errors} />
      <div className="grid grid-cols-2 gap-4">
        <Field name="city" label="City" defaultValue={address?.city ?? ""} required errors={errors} />
        <Field name="state" label="State" defaultValue={address?.state ?? ""} required errors={errors} />
      </div>
      <div className="grid grid-cols-2 gap-4">
        <Field name="postal_code" label="Postal Code" defaultValue={address?.postal_code ?? ""} required errors={errors} />
        <Field name="country" label="Country" defaultValue={address?.country ?? "India"} required errors={errors} />
      </div>
      <label className="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_default" value="1" defaultChecked={address?.is_default ?? false} className="rounded border-gray-300 text-blue-600" />
        Make this my default address
      </label>
      <div className="flex items-center gap-3 pt-2">
        <button disabled={pending} className="rounded-lg bg-blue-600 px-6 py-2.5 font-medium text-white hover:bg-blue-700 disabled:opacity-70">
          {pending ? "Saving…" : "Save Address"}
        </button>
        <Link href="/account/addresses" className="text-sm text-gray-500">Cancel</Link>
      </div>
    </form>
  );
}
