/**
 * Seconds until a JWT's `exp` claim, or -1 if it can't be read. Only used to
 * decide when to refresh the session; the Laravel API verifies every token.
 */
export function secondsUntilExpiry(token: string): number {
  try {
    const payload = token.split(".")[1];
    const json = JSON.parse(atob(payload.replace(/-/g, "+").replace(/_/g, "/")));
    if (typeof json.exp !== "number") return -1;
    return json.exp - Math.floor(Date.now() / 1000);
  } catch {
    return -1;
  }
}
