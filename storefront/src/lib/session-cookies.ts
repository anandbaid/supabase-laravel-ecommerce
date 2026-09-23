// Cookie names and options for the Supabase session, shared by proxy.ts
// (which refreshes it) and server actions (which set/clear it on login/logout).

export const ACCESS_COOKIE = "ls_session";
export const REFRESH_COOKIE = "ls_refresh";

/** Refresh the access token when it has less than this many seconds left. */
export const REFRESH_MARGIN_SECONDS = 120;

const REFRESH_MAX_AGE = 60 * 60 * 24 * 30; // 30 days

const base = {
  httpOnly: true,
  secure: process.env.NODE_ENV === "production",
  sameSite: "lax" as const,
  path: "/",
};

export function accessCookieOptions(expiresIn?: number | null) {
  // Keep the cookie a little longer than the token so the proxy can still
  // see it's expired and swap it for a fresh one.
  return { ...base, maxAge: Math.max(60, (expiresIn ?? 3600) + 300) };
}

export function refreshCookieOptions() {
  return { ...base, maxAge: REFRESH_MAX_AGE };
}
