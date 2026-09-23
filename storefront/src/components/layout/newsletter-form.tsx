"use client";

import { useEffect, useRef } from "react";
import { subscribeNewsletter } from "@/app/actions/shop";
import { useToast } from "@/components/providers/toast";
import { useFormAction } from "@/components/ui/use-form-action";

export function NewsletterForm() {
  const [state, onSubmit, pending] = useFormAction(subscribeNewsletter, null);
  const toast = useToast();
  const form = useRef<HTMLFormElement>(null);

  useEffect(() => {
    if (!state) return;
    toast(state.ok ? (state.message ?? "Subscribed!") : state.message, state.ok ? "success" : "error");
    if (state.ok) form.current?.reset();
  }, [state, toast]);

  return (
    <form ref={form} onSubmit={onSubmit} className="flex">
      <input
        type="email"
        name="email"
        required
        placeholder="Your email address"
        aria-label="Email address"
        className="w-full rounded-l-lg border-0 px-3 py-2 text-sm text-gray-800"
      />
      <button
        type="submit"
        disabled={pending}
        className="whitespace-nowrap rounded-r-lg bg-blue-600 px-4 text-sm text-white disabled:opacity-70"
      >
        {pending ? "…" : "Subscribe"}
      </button>
    </form>
  );
}
