"use client";

import Link from "next/link";
import { useSession } from "@/components/providers/session";

export function FooterAccountLinks() {
  const { user } = useSession();
  if (user?.is_admin) return null;

  return (
    <>
      <li><Link href="/account/orders" className="hover:text-white">Track Order</Link></li>
      <li><Link href="/account" className="hover:text-white">My Account</Link></li>
    </>
  );
}
