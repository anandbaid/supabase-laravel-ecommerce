import { NextResponse, type NextRequest } from "next/server";
import { secondsUntilExpiry } from "./lib/jwt";
import {
  ACCESS_COOKIE,
  REFRESH_COOKIE,
  REFRESH_MARGIN_SECONDS,
  accessCookieOptions,
  refreshCookieOptions,
} from "./lib/session-cookies";

/** Pages that need a signed-in customer. */
const PROTECTED = [/^\/account(\/|$)/, /^\/checkout(\/|$)/];

/**
 * Keeps the Supabase session fresh: when the access token is about to
 * expire, trade the refresh token for a new pair (via the Laravel API) and
 * hand the new token to this request's Server Components as well as the
 * browser. Also sends guests on protected pages to the login page.
 */
export async function proxy(request: NextRequest) {
  let response = NextResponse.next();

  const access = request.cookies.get(ACCESS_COOKIE)?.value;
  const refresh = request.cookies.get(REFRESH_COOKIE)?.value;

  const accessStillValid = Boolean(access) && secondsUntilExpiry(access!) > 0;
  // Prefetches can be cancelled before their Set-Cookie reaches the browser,
  // so while the current token still works, leave refreshing to real navigations.
  const isPrefetch =
    request.headers.has("next-router-prefetch") ||
    request.headers.has("next-router-segment-prefetch") ||
    Boolean(request.headers.get("sec-purpose")?.includes("prefetch"));

  if (refresh && (!access || secondsUntilExpiry(access) < REFRESH_MARGIN_SECONDS) && !(isPrefetch && accessStillValid)) {
    const refreshed = await refreshOnce(refresh, request);

    // A rejected refresh token only ends the session once the access token is
    // unusable too. While it's still valid, the rejection is most likely a
    // parallel request (e.g. a prefetch) that raced another one which already
    // rotated the refresh token — that request's new cookies win.
    if (refreshed === "invalid" && !accessStillValid) {
      request.cookies.delete(ACCESS_COOKIE);
      request.cookies.delete(REFRESH_COOKIE);
      response = NextResponse.next({ request });
      response.cookies.delete(ACCESS_COOKIE);
      response.cookies.delete(REFRESH_COOKIE);
    } else if (refreshed && refreshed !== "invalid") {
      request.cookies.set(ACCESS_COOKIE, refreshed.access_token);
      request.cookies.set(REFRESH_COOKIE, refreshed.refresh_token);
      response = NextResponse.next({ request });
      response.cookies.set(ACCESS_COOKIE, refreshed.access_token, accessCookieOptions(refreshed.expires_in));
      response.cookies.set(REFRESH_COOKIE, refreshed.refresh_token, refreshCookieOptions());
    }
  }

  const { pathname, search } = request.nextUrl;
  if (PROTECTED.some((re) => re.test(pathname)) && !request.cookies.get(ACCESS_COOKIE)) {
    const login = new URL("/login", request.url);
    login.searchParams.set("next", pathname + search);
    const redirect = NextResponse.redirect(login);
    // Keep any cookie changes made above.
    response.cookies.getAll().forEach((c) => redirect.cookies.set(c));
    return redirect;
  }

  return response;
}

type Refreshed = { access_token: string; refresh_token: string; expires_in: number | null };
type RefreshResult = Refreshed | "invalid" | null;

/**
 * Refresh tokens are single-use, and a page load fires several requests at
 * once. Requests arriving with the same refresh token share one refresh
 * (and all hand the browser the same new pair) instead of racing each other.
 */
const inflight = new Map<string, { startedAt: number; result: Promise<RefreshResult> }>();
const SHARE_MS = 10_000;

function refreshOnce(refreshToken: string, request: NextRequest): Promise<RefreshResult> {
  const now = Date.now();
  for (const [token, entry] of inflight) if (now - entry.startedAt > SHARE_MS) inflight.delete(token);

  let entry = inflight.get(refreshToken);
  if (!entry) {
    entry = { startedAt: now, result: refreshSession(refreshToken, request) };
    inflight.set(refreshToken, entry);
  }
  return entry.result;
}

async function refreshSession(refreshToken: string, request: NextRequest): Promise<RefreshResult> {
  const base = process.env.LARAVEL_API_URL?.replace(/\/+$/, "");
  if (!base) return null;

  try {
    const res = await fetch(`${base}/auth/refresh`, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        ...(request.headers.get("x-forwarded-for")
          ? { "X-Forwarded-For": request.headers.get("x-forwarded-for")!.split(",")[0].trim() }
          : {}),
      },
      body: JSON.stringify({ refresh_token: refreshToken }),
      cache: "no-store",
    });
    if (res.status === 401 || res.status === 422) return "invalid";
    if (!res.ok) return null; // temporary problem: keep the session, try again next request
    return (await res.json()) as Refreshed;
  } catch {
    return null;
  }
}

export const config = {
  // Skip static files and images.
  matcher: ["/((?!_next/static|_next/image|images/|favicon.ico|robots.txt).*)"],
};
