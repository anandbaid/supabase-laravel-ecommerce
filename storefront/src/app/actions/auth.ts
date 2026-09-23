"use server";

import { redirect } from "next/navigation";
import { api, failure } from "@/lib/api";
import { safeNextPath } from "@/lib/next-param";
import { clearSession, getAccessToken, saveSession } from "@/lib/session";
import type { ActionResult, User } from "@/lib/types";

type Session = { access_token: string; refresh_token: string; expires_in: number | null; user: User };

function safeNext(next: FormDataEntryValue | null): string {
  return safeNextPath(typeof next === "string" ? next : undefined);
}

export async function login(_prev: ActionResult | null, form: FormData): Promise<ActionResult> {
  const res = await api<Session>("/auth/login", {
    method: "POST",
    auth: false,
    body: { email: form.get("email"), password: form.get("password") },
  });
  if (!res.ok) return failure(res);

  await saveSession(res.data);
  redirect(safeNext(form.get("next")));
}

export async function register(_prev: ActionResult | null, form: FormData): Promise<ActionResult> {
  const res = await api<Session & { needs_confirmation?: boolean; message?: string }>("/auth/register", {
    method: "POST",
    auth: false,
    body: {
      name: form.get("name"),
      email: form.get("email"),
      password: form.get("password"),
      password_confirmation: form.get("password_confirmation"),
    },
  });
  if (!res.ok) return failure(res);

  if (res.data.needs_confirmation) {
    return {
      ok: true,
      data: null,
      message: res.data.message ?? "Account created! Please check your email to confirm your address before logging in.",
    };
  }

  await saveSession(res.data);
  redirect(safeNext(form.get("next")));
}

export async function logout(): Promise<void> {
  if (await getAccessToken()) {
    await api("/auth/logout", { method: "POST" });
  }
  await clearSession();
  redirect("/");
}
