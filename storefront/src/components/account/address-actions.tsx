"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useTransition } from "react";
import { deleteAddress, makeDefaultAddress } from "@/app/actions/account";
import { useToast } from "@/components/providers/toast";
import type { Address } from "@/lib/types";

export function AddressCardActions({ address }: { address: Address }) {
  const router = useRouter();
  const toast = useToast();
  const [pending, start] = useTransition();

  const run = (fn: () => ReturnType<typeof deleteAddress>) =>
    start(async () => {
      const res = await fn();
      toast(res.ok ? (res.message ?? "Saved.") : res.message, res.ok ? "success" : "error");
      if (res.ok) router.refresh();
    });

  return (
    <div className="mt-4 flex items-center gap-3 text-sm">
      <Link href={`/account/addresses/${address.id}/edit`} className="text-blue-600 hover:underline">Edit</Link>
      {!address.is_default && (
        <button type="button" disabled={pending} onClick={() => run(() => makeDefaultAddress(address.id))} className="text-gray-600 hover:underline">
          Set as Default
        </button>
      )}
      <button
        type="button"
        disabled={pending}
        onClick={() => window.confirm("Remove this address?") && run(() => deleteAddress(address.id))}
        className="text-red-500 hover:underline"
      >
        Remove
      </button>
    </div>
  );
}
