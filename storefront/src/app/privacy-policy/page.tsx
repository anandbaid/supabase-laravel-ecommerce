import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = { title: "Privacy Policy" };

export default function PrivacyPage() {
  const updated = new Date().toLocaleDateString("en-US", { month: "long", year: "numeric" });
  const text = "text-sm leading-relaxed text-gray-600";

  return (
    <div className="mx-auto max-w-4xl px-4 py-12">
      <h1 className="mb-2 text-3xl font-bold">Privacy Policy</h1>
      <p className="mb-8 text-gray-500">Your Privacy Matters</p>
      <div className="space-y-8 rounded-xl bg-white p-6 shadow-sm sm:p-8">
        <section>
          <h2 className="mb-2 text-lg font-semibold">1. Information We Collect</h2>
          <p className={text}>
            We collect information you provide directly to us, such as your name, email address, shipping address,
            and payment details, when you create an account or place an order.
          </p>
        </section>
        <section>
          <h2 className="mb-2 text-lg font-semibold">2. How We Use Your Information</h2>
          <ul className={`${text} list-inside list-disc space-y-1`}>
            <li>Process your orders and deliver products.</li>
            <li>Communicate order updates and customer service.</li>
            <li>Send promotional offers (only with your consent).</li>
            <li>Improve and personalize your shopping experience.</li>
          </ul>
        </section>
        <section>
          <h2 className="mb-2 text-lg font-semibold">3. Cookies</h2>
          <p className={text}>
            This site uses cookies and your browser&apos;s storage to remember your cart, preferences, and login
            session. You can disable them in your browser, though some features may not work correctly.
          </p>
        </section>
        <section>
          <h2 className="mb-2 text-lg font-semibold">4. Sharing Your Information</h2>
          <p className={text}>
            We never sell your personal information. We only share it with trusted service providers (like payment
            processors and delivery partners) as needed to fulfil your order.
          </p>
        </section>
        <section>
          <h2 className="mb-2 text-lg font-semibold">5. Your Rights</h2>
          <p className={text}>
            You can access, update, or delete your account information at any time from your{" "}
            <Link href="/account/profile" className="text-blue-600 hover:underline">Account settings</Link>, or contact
            us if you need help.
          </p>
        </section>
        <p className="border-t pt-4 text-xs text-gray-400">Last updated: {updated}</p>
      </div>
    </div>
  );
}
