import "server-only";

import { headers } from "next/headers";
import { apiBaseUrl } from "./config";
import { getAccessToken } from "./session";

export type ApiSuccess<T> = { ok: true; status: number; data: T };
export type ApiFailure = {
  ok: false;
  status: number;
  message: string;
  errors?: Record<string, string[]>;
  body?: Record<string, unknown>;
};
export type ApiResponse<T> = ApiSuccess<T> | ApiFailure;

type Options = {
  method?: "GET" | "POST" | "PUT" | "PATCH" | "DELETE";
  body?: unknown;
  /** Send the customer's session token (default true). */
  auth?: boolean;
  /** Cache a public GET for this many seconds (never used with auth). */
  revalidate?: number;
};

/**
 * Call the Laravel storefront API from the server (Server Components and
 * Server Actions). The browser never talks to Laravel directly, so the
 * session token stays in an httpOnly cookie.
 */
export async function api<T>(path: string, options: Options = {}): Promise<ApiResponse<T>> {
  const { method = "GET", body, auth = true, revalidate } = options;
  const cacheable = method === "GET" && !auth && revalidate !== undefined;

  const requestHeaders: Record<string, string> = { Accept: "application/json" };
  if (body !== undefined) requestHeaders["Content-Type"] = "application/json";

  if (auth) {
    const token = await getAccessToken();
    if (token) requestHeaders.Authorization = `Bearer ${token}`;
  }

  if (!cacheable) {
    // Pass the shopper's IP along so Laravel's rate limits apply per
    // customer rather than to this server as a whole.
    const forwarded = (await headers()).get("x-forwarded-for");
    const clientIp = forwarded?.split(",")[0]?.trim();
    if (clientIp) requestHeaders["X-Forwarded-For"] = clientIp;
  }

  let response: Response;
  try {
    response = await fetch(`${apiBaseUrl()}${path}`, {
      method,
      headers: requestHeaders,
      body: body === undefined ? undefined : JSON.stringify(body),
      ...(cacheable ? { next: { revalidate } } : { cache: "no-store" as const }),
    });
  } catch (error) {
    console.error(`API ${method} ${path} failed`, error);
    return { ok: false, status: 503, message: "We couldn't reach the store right now. Please try again in a moment." };
  }

  const text = await response.text();
  let json: Record<string, unknown> | null = null;
  try {
    json = text ? JSON.parse(text) : null;
  } catch {
    json = null;
  }

  if (response.ok) {
    return { ok: true, status: response.status, data: json as T };
  }

  if (response.status >= 500) {
    console.error(`API ${method} ${path} -> ${response.status}`, text.slice(0, 500));
  }

  return {
    ok: false,
    status: response.status,
    message:
      (json?.message as string | undefined) ||
      (response.status === 404 ? "Not found." : "Something went wrong. Please try again."),
    errors: json?.errors as Record<string, string[]> | undefined,
    body: json ?? undefined,
  };
}

/** For Server Actions: turn an API failure into an ActionResult. */
export function failure(res: ApiFailure) {
  return { ok: false as const, message: res.message, errors: res.errors, status: res.status };
}
