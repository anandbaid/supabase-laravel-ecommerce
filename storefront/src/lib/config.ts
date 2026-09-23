/** Server-side configuration, read from environment variables. */
export function apiBaseUrl(): string {
  const url = process.env.LARAVEL_API_URL;
  if (!url) {
    throw new Error(
      "LARAVEL_API_URL is not set. Point it at the Laravel API, e.g. https://your-laravel-app.com/api/v1",
    );
  }
  return url.replace(/\/+$/, "");
}

/** Where the Laravel admin panel lives (shown to admin accounts). */
export const adminPanelUrl = process.env.NEXT_PUBLIC_ADMIN_URL || "";

export const siteName = process.env.NEXT_PUBLIC_SITE_NAME || "Let's Shop";
