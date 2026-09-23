"use client";

import { useFormAction } from "@/components/ui/use-form-action";
import Link from "next/link";
import { login, register } from "@/app/actions/auth";
import { Alert, FieldError, inputClass } from "@/components/ui/form";

export function LoginForm({ next, notice }: { next: string; notice?: string }) {
  const [state, onSubmit, pending] = useFormAction(login, null);
  const error = state && !state.ok ? state.message : null;

  return (
    <form onSubmit={onSubmit} className="space-y-4">
      {notice && !error && <Alert tone="info">{notice}</Alert>}
      {error && <Alert tone="error">{error}</Alert>}
      <input type="hidden" name="next" value={next} />
      <label className="block text-sm font-medium">
        Email
        <input type="email" name="email" required autoComplete="email" className={`${inputClass} mt-1`} />
      </label>
      <label className="block text-sm font-medium">
        Password
        <input type="password" name="password" required autoComplete="current-password" className={`${inputClass} mt-1`} />
      </label>
      <button disabled={pending} className="w-full rounded-lg bg-blue-600 py-2.5 font-medium text-white hover:bg-blue-700 disabled:opacity-70">
        {pending ? "Signing in…" : "Login"}
      </button>
      <p className="mt-6 text-center text-sm text-gray-500">
        Don&apos;t have an account?{" "}
        <Link href={`/register${next !== "/" ? `?next=${encodeURIComponent(next)}` : ""}`} className="font-medium text-blue-600">
          Register
        </Link>
      </p>
    </form>
  );
}

export function RegisterForm({ next }: { next: string }) {
  const [state, onSubmit, pending] = useFormAction(register, null);
  const errors = state && !state.ok ? state.errors : undefined;

  if (state?.ok) {
    return (
      <div className="space-y-4">
        <Alert tone="success">{state.message}</Alert>
        <Link href="/login" className="block text-center text-sm font-medium text-blue-600">Go to login</Link>
      </div>
    );
  }

  return (
    <form onSubmit={onSubmit} className="space-y-4">
      {state && !state.ok && !errors && <Alert tone="error">{state.message}</Alert>}
      <input type="hidden" name="next" value={next} />
      <label className="block text-sm font-medium">
        Full Name
        <input name="name" required autoComplete="name" className={`${inputClass} mt-1`} />
        <FieldError errors={errors} name="name" />
      </label>
      <label className="block text-sm font-medium">
        Email
        <input type="email" name="email" required autoComplete="email" className={`${inputClass} mt-1`} />
        <FieldError errors={errors} name="email" />
      </label>
      <label className="block text-sm font-medium">
        Password
        <input type="password" name="password" required minLength={6} autoComplete="new-password" className={`${inputClass} mt-1`} />
        <FieldError errors={errors} name="password" />
      </label>
      <label className="block text-sm font-medium">
        Confirm Password
        <input type="password" name="password_confirmation" required minLength={6} autoComplete="new-password" className={`${inputClass} mt-1`} />
      </label>
      <button disabled={pending} className="w-full rounded-lg bg-blue-600 py-2.5 font-medium text-white hover:bg-blue-700 disabled:opacity-70">
        {pending ? "Creating account…" : "Register"}
      </button>
      <p className="mt-6 text-center text-sm text-gray-500">
        Already have an account? <Link href="/login" className="font-medium text-blue-600">Login</Link>
      </p>
    </form>
  );
}
