import "server-only";

import { cookies } from "next/headers";
import {
  ACCESS_COOKIE,
  REFRESH_COOKIE,
  accessCookieOptions,
  refreshCookieOptions,
} from "./session-cookies";

export async function getAccessToken(): Promise<string | undefined> {
  return (await cookies()).get(ACCESS_COOKIE)?.value;
}

/** Only callable from Server Actions / Route Handlers. */
export async function saveSession(session: {
  access_token: string;
  refresh_token: string;
  expires_in?: number | null;
}) {
  const jar = await cookies();
  jar.set(ACCESS_COOKIE, session.access_token, accessCookieOptions(session.expires_in));
  jar.set(REFRESH_COOKIE, session.refresh_token, refreshCookieOptions());
}

/** Only callable from Server Actions / Route Handlers. */
export async function clearSession() {
  const jar = await cookies();
  jar.delete(ACCESS_COOKIE);
  jar.delete(REFRESH_COOKIE);
}
