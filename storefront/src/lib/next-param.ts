/** Sanitise a ?next= redirect target so it can only point inside this site. */
export function safeNextPath(value: string | string[] | undefined): string {
  const v = Array.isArray(value) ? value[0] : value;
  if (!v || !v.startsWith("/") || v.startsWith("//") || v.includes("\\")) return "/";
  return v;
}
