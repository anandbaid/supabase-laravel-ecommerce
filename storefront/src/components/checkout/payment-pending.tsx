"use client";

import { useRouter } from "next/navigation";
import { useEffect } from "react";

/** While Stripe's webhook hasn't marked the order paid yet, re-check every few seconds. */
export function PaymentPending() {
  const router = useRouter();
  useEffect(() => {
    let tries = 0;
    const timer = window.setInterval(() => {
      if (++tries > 20) window.clearInterval(timer);
      router.refresh();
    }, 3000);
    return () => window.clearInterval(timer);
  }, [router]);
  return null;
}
