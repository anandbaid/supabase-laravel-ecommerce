import Image from "next/image";
import Link from "next/link";
import { ArrowRight, RotateCcw, ShieldCheck, Truck } from "lucide-react";
import { ProductGrid } from "@/components/shop/product-card";
import { RecentlyViewed } from "@/components/shop/recently-viewed";
import { api } from "@/lib/api";
import { getSettings } from "@/lib/data";
import { trimNumber } from "@/lib/format";
import type { Category, Product } from "@/lib/types";

type Home = { categories: Category[]; featured: Product[]; latest: Product[] };

export default async function HomePage() {
  const [res, settings] = await Promise.all([
    api<Home>("/home", { auth: false, revalidate: 30 }),
    getSettings(),
  ]);
  const home: Home = res.ok ? res.data : { categories: [], featured: [], latest: [] };

  return (
    <>
      <section className="relative overflow-hidden bg-blue-50">
        <Image
          src="/images/hero-banner.png"
          alt=""
          fill
          priority
          sizes="100vw"
          className="pointer-events-none select-none object-cover object-right"
        />
        <div className="relative mx-auto max-w-7xl px-4 py-20 md:py-28">
          <div className="max-w-xl">
            <span className="mb-4 inline-block rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">NEW ARRIVALS</span>
            <h1 className="mb-4 text-4xl font-extrabold leading-tight text-gray-900 md:text-5xl">
              Upgrade Your <span className="text-blue-600">Everyday Style</span>
            </h1>
            <p className="mb-6 max-w-md text-gray-600">
              Discover the latest trends in fashion, electronics, home essentials and more — all in one place.
            </p>
            <Link href="/shop" className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-3 font-medium text-white transition hover:bg-blue-700">
              Shop Now <ArrowRight className="h-4 w-4" />
            </Link>
            <div className="mt-8 flex flex-wrap gap-6 text-sm text-gray-600">
              <div className="flex items-start gap-2">
                <Truck className="mt-0.5 h-5 w-5 text-blue-600" />
                <div>Free Shipping<br /><span className="text-xs text-gray-400">On orders over ${trimNumber(settings.free_shipping_threshold)}</span></div>
              </div>
              <div className="flex items-start gap-2">
                <ShieldCheck className="mt-0.5 h-5 w-5 text-blue-600" />
                <div>Secure Payment<br /><span className="text-xs text-gray-400">100% protected</span></div>
              </div>
              <div className="flex items-start gap-2">
                <RotateCcw className="mt-0.5 h-5 w-5 text-blue-600" />
                <div>Easy Returns<br /><span className="text-xs text-gray-400">Within 7 days</span></div>
              </div>
            </div>
          </div>
        </div>
        <div className="absolute right-6 top-10 hidden rotate-3 rounded-2xl rounded-br-sm bg-blue-600 px-4 py-3 text-sm font-semibold leading-snug text-white shadow-lg sm:block md:right-16 md:top-14">
          Best Deals<br /><span className="text-base">Up to 50% OFF</span>
        </div>
      </section>

      {!res.ok && (
        <p className="mx-auto mt-8 max-w-7xl px-4 text-sm text-red-600">{res.message}</p>
      )}

      {home.categories.length > 0 && (
        <section className="mx-auto max-w-7xl px-4 py-12">
          <div className="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-8">
            {home.categories.map((cat) => (
              <Link
                key={cat.id}
                href={`/shop?category=${cat.slug}`}
                className="group rounded-xl border border-gray-100 bg-white p-5 text-center shadow-sm transition hover:bg-blue-50"
              >
                <div className="mx-auto mb-3 h-16 w-16 overflow-hidden rounded-lg bg-gray-50">
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img src={cat.image} alt={cat.name} className="h-full w-full object-cover" />
                </div>
                <div className="text-sm font-medium">{cat.name}</div>
              </Link>
            ))}
          </div>
        </section>
      )}

      <section className="mx-auto max-w-7xl px-4 py-8">
        <div className="mb-6 flex items-center justify-between">
          <h2 className="text-2xl font-bold">Featured Products</h2>
          <Link href="/shop" className="flex items-center gap-1 text-sm font-medium text-blue-600">
            View All <ArrowRight className="h-3.5 w-3.5" />
          </Link>
        </div>
        {home.featured.length ? (
          <ProductGrid products={home.featured} className="grid grid-cols-2 gap-5 md:grid-cols-3 lg:grid-cols-6" />
        ) : (
          <p className="text-gray-400">No featured products yet.</p>
        )}
      </section>

      <section className="mx-auto max-w-7xl px-4 py-8">
        <h2 className="mb-6 text-2xl font-bold">Latest Products</h2>
        {home.latest.length ? (
          <ProductGrid products={home.latest} className="grid grid-cols-2 gap-5 md:grid-cols-3 lg:grid-cols-4" />
        ) : (
          <p className="text-gray-400">No products yet.</p>
        )}
      </section>

      <RecentlyViewed />
    </>
  );
}
