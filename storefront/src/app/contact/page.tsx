import type { Metadata } from "next";
import { Clock, Mail, MapPin, Phone } from "lucide-react";
import { ContactForm } from "@/components/contact-form";
import { getMe } from "@/lib/data";

export const metadata: Metadata = { title: "Contact Us" };

export default async function ContactPage() {
  const me = await getMe();
  const details = [
    { Icon: MapPin, label: "Address", value: "123 Shopping Street, Kolkata, India" },
    { Icon: Mail, label: "Email", value: "support@letsshop.com" },
    { Icon: Phone, label: "Phone", value: "+91 98765 43210" },
    { Icon: Clock, label: "Business Hours", value: "Mon – Sat, 9:00 AM – 8:00 PM" },
  ];

  return (
    <div className="mx-auto max-w-5xl px-4 py-12">
      <h1 className="mb-2 text-3xl font-bold">Contact Us</h1>
      <p className="mb-8 text-gray-500">We&apos;re here to help. Reach out to us anytime.</p>
      <div className="grid gap-8 md:grid-cols-2">
        <div className="space-y-5 rounded-xl bg-white p-6 shadow-sm">
          {details.map(({ Icon, label, value }) => (
            <div key={label} className="flex items-start gap-3">
              <Icon className="mt-0.5 h-5 w-5 text-blue-600" />
              <div>
                <div className="text-sm font-semibold">{label}</div>
                <div className="text-sm text-gray-500">{value}</div>
              </div>
            </div>
          ))}
        </div>
        <ContactForm name={me?.user.name ?? ""} email={me?.user.email ?? ""} />
      </div>
    </div>
  );
}
