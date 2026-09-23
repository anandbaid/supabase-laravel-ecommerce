import type { Metadata } from "next";
import { BadgeCheck, ShoppingBag, Smile, Tag } from "lucide-react";

export const metadata: Metadata = { title: "About Us" };

export default function AboutPage() {
  const cards = [
    { Icon: BadgeCheck, title: "Best Quality", text: "Top rated products, checked and verified." },
    { Icon: Tag, title: "Affordable Prices", text: "Save more, shop more, every single day." },
    { Icon: Smile, title: "Happy Customers", text: "Thousands of satisfied shoppers and counting." },
  ];

  return (
    <div className="mx-auto max-w-6xl px-4 py-12">
      <div className="mb-12 grid items-center gap-10 md:grid-cols-2">
        <div>
          <h1 className="mb-3 text-3xl font-bold">About Us</h1>
          <p className="mb-3 text-gray-500">Your Trusted Online Shopping Partner</p>
          <p className="leading-relaxed text-gray-600">
            We are passionate about bringing you the best products at the best prices. Our mission is to make online
            shopping easy, affordable, and enjoyable for everyone — from everyday essentials to the things you
            didn&apos;t know you needed.
          </p>
        </div>
        <div className="flex items-center justify-center rounded-2xl bg-blue-50 p-10">
          <ShoppingBag className="h-28 w-28 text-blue-400" />
        </div>
      </div>
      <div className="grid gap-6 sm:grid-cols-3">
        {cards.map(({ Icon, title, text }) => (
          <div key={title} className="rounded-xl bg-white p-6 text-center shadow-sm">
            <Icon className="mx-auto mb-3 h-8 w-8 text-blue-600" />
            <h3 className="mb-1 font-semibold">{title}</h3>
            <p className="text-sm text-gray-500">{text}</p>
          </div>
        ))}
      </div>
    </div>
  );
}
