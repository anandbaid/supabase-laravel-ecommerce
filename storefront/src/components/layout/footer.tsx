import Image from "next/image";
import Link from "next/link";
import { siteName } from "@/lib/config";
import { FooterAccountLinks } from "./footer-account-links";
import { NewsletterForm } from "./newsletter-form";

const SOCIAL = [
  { label: "Facebook", path: "M14 8h2V5h-2c-2.2 0-4 1.8-4 4v2H8v3h2v7h3v-7h2.5l.5-3h-3V9c0-.6.4-1 1-1z" },
  { label: "X (Twitter)", path: "M5 5h3.6l3.6 5 4.2-5H18l-5 6 5.5 8h-3.6l-3.9-5.4L6.4 19H5l5.6-6.6z" },
  { label: "Instagram", path: "M8 4h8a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8a4 4 0 0 1 4-4zm4 5a3 3 0 1 0 0 6 3 3 0 0 0 0-6zm4.5-2.2a1 1 0 1 0 0 2 1 1 0 0 0 0-2z" },
  { label: "LinkedIn", path: "M6.5 9H4v11h2.5zM5.2 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM20 13.6c0-3-1.6-4.6-3.9-4.6-1.3 0-2.3.7-2.8 1.5V9H11v11h2.4v-5.8c0-1.4.7-2.4 2-2.4 1.2 0 1.8.9 1.8 2.4V20H20z" },
];

export function Footer() {
  return (
    <footer className="mt-16 bg-slate-900 text-gray-300">
      <div className="mx-auto grid max-w-7xl grid-cols-1 gap-8 px-4 py-12 md:grid-cols-5">
        <div>
          <div className="mb-3 inline-block rounded-lg bg-white px-3 py-2">
            <Image src="/images/logo.png" alt={siteName} width={112} height={28} className="h-7 w-auto" />
          </div>
          <p className="text-sm text-gray-400">Better Products. Happier You.</p>
          <div className="mt-4 flex items-center gap-3">
            {SOCIAL.map((s) => (
              <a
                key={s.label}
                href="#"
                aria-label={s.label}
                className="flex h-8 w-8 items-center justify-center rounded-full bg-slate-800 transition hover:bg-blue-600"
              >
                <svg viewBox="0 0 24 24" className="h-4 w-4 fill-current" aria-hidden>
                  <path d={s.path} />
                </svg>
              </a>
            ))}
          </div>
        </div>

        <div>
          <h4 className="mb-3 font-semibold text-white">Quick Links</h4>
          <ul className="space-y-2 text-sm text-gray-400">
            <li><Link href="/" className="hover:text-white">Home</Link></li>
            <li><Link href="/shop" className="hover:text-white">Shop</Link></li>
            <li><Link href="/shop?deals=1" className="hover:text-white">Deals</Link></li>
            <li><Link href="/about" className="hover:text-white">About Us</Link></li>
            <li><Link href="/contact" className="hover:text-white">Contact</Link></li>
          </ul>
        </div>

        <div>
          <h4 className="mb-3 font-semibold text-white">Information</h4>
          <ul className="space-y-2 text-sm text-gray-400">
            <li><Link href="/privacy-policy" className="hover:text-white">Privacy Policy</Link></li>
            <li><Link href="/privacy-policy" className="hover:text-white">Terms &amp; Conditions</Link></li>
            <li><Link href="/return-policy" className="hover:text-white">Return Policy</Link></li>
            <li><Link href="/return-policy" className="hover:text-white">Shipping Policy</Link></li>
          </ul>
        </div>

        <div>
          <h4 className="mb-3 font-semibold text-white">Customer Service</h4>
          <ul className="space-y-2 text-sm text-gray-400">
            <li><Link href="/contact" className="hover:text-white">Help Center</Link></li>
            <FooterAccountLinks />
            <li><Link href="/return-policy" className="hover:text-white">Returns &amp; Refunds</Link></li>
            <li><Link href="/contact" className="hover:text-white">Support</Link></li>
          </ul>
        </div>

        <div>
          <h4 className="mb-3 font-semibold text-white">Newsletter</h4>
          <p className="mb-2 text-sm text-gray-400">Subscribe to get updates and offers.</p>
          <NewsletterForm />
        </div>
      </div>

      <div className="border-t border-gray-800">
        <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 py-4 sm:flex-row">
          <p className="text-xs text-gray-500">&copy; {new Date().getFullYear()} {siteName}. All rights reserved.</p>
          <div className="flex items-center gap-3 text-xs font-semibold tracking-wide text-gray-500">
            {["VISA", "Mastercard", "Stripe"].map((m) => (
              <span key={m} className="rounded border border-gray-700 px-2 py-1">{m}</span>
            ))}
          </div>
        </div>
      </div>
    </footer>
  );
}
