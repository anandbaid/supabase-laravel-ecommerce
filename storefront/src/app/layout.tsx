import type { Metadata } from "next";
import localFont from "next/font/local";
import "./globals.css";
import { Footer } from "@/components/layout/footer";
import { Header } from "@/components/layout/header";
import { CartProvider } from "@/components/providers/cart";
import { SessionProvider } from "@/components/providers/session";
import { ToastProvider } from "@/components/providers/toast";
import { siteName } from "@/lib/config";
import { getMe } from "@/lib/data";

// Poppins, self-hosted from @fontsource so builds don't depend on Google Fonts.
const poppins = localFont({
  src: [
    { path: "../../node_modules/@fontsource/poppins/files/poppins-latin-400-normal.woff2", weight: "400", style: "normal" },
    { path: "../../node_modules/@fontsource/poppins/files/poppins-latin-500-normal.woff2", weight: "500", style: "normal" },
    { path: "../../node_modules/@fontsource/poppins/files/poppins-latin-600-normal.woff2", weight: "600", style: "normal" },
    { path: "../../node_modules/@fontsource/poppins/files/poppins-latin-700-normal.woff2", weight: "700", style: "normal" },
    { path: "../../node_modules/@fontsource/poppins/files/poppins-latin-800-normal.woff2", weight: "800", style: "normal" },
  ],
  variable: "--font-poppins",
  display: "swap",
});

export const metadata: Metadata = {
  title: { default: `${siteName} - Better Products. Happier You.`, template: `%s - ${siteName}` },
  description: "Discover the latest trends in fashion, electronics, home essentials and more — all in one place.",
};

export default async function RootLayout({ children }: LayoutProps<"/">) {
  const me = await getMe();

  return (
    <html lang="en" className={poppins.variable}>
      <body className="min-h-screen bg-gray-50 font-sans text-gray-800 antialiased">
        <ToastProvider>
          <SessionProvider user={me?.user ?? null} wishlistIds={me?.wishlist_ids ?? []}>
            <CartProvider>
              <Header />
              <main>{children}</main>
              <Footer />
            </CartProvider>
          </SessionProvider>
        </ToastProvider>
      </body>
    </html>
  );
}
