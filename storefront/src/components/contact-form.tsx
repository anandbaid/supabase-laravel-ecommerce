"use client";

import { useFormAction } from "@/components/ui/use-form-action";
import { sendContactMessage } from "@/app/actions/shop";
import { Alert, FieldError, inputClass } from "@/components/ui/form";

export function ContactForm({ name, email }: { name: string; email: string }) {
  const [state, onSubmit, pending] = useFormAction(sendContactMessage, null);
  const errors = state && !state.ok ? state.errors : undefined;

  if (state?.ok) {
    return (
      <div className="rounded-xl bg-white p-6 shadow-sm">
        <Alert tone="success">{state.message}</Alert>
      </div>
    );
  }

  return (
    <form onSubmit={onSubmit} className="space-y-4 rounded-xl bg-white p-6 shadow-sm">
      {state && !state.ok && !errors && <Alert tone="error">{state.message}</Alert>}
      <div className="grid gap-4 sm:grid-cols-2">
        <label className="text-sm font-medium">
          Name
          <input type="text" name="name" defaultValue={name} required className={`${inputClass} mt-1`} />
          <FieldError errors={errors} name="name" />
        </label>
        <label className="text-sm font-medium">
          Email
          <input type="email" name="email" defaultValue={email} required className={`${inputClass} mt-1`} />
          <FieldError errors={errors} name="email" />
        </label>
      </div>
      <label className="block text-sm font-medium">
        Message
        <textarea name="message" rows={5} required maxLength={5000} className={`${inputClass} mt-1`} />
        <FieldError errors={errors} name="message" />
      </label>
      <button disabled={pending} className="w-full rounded-lg bg-blue-600 py-2.5 font-medium text-white hover:bg-blue-700 disabled:opacity-70">
        {pending ? "Sending…" : "Send Message"}
      </button>
    </form>
  );
}
