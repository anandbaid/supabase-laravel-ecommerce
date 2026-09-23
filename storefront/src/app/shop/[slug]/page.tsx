import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { cache } from "react";
import { Headphones, RefreshCw, ShieldCheck, Truck } from "lucide-react";
import { BuyBox } from "@/components/shop/buy-box";
import { ProductGallery } from "@/components/shop/product-gallery";
import { ProductTabs } from "@/components/shop/product-tabs";
import { ProductGrid } from "@/components/shop/product-card";
import { RecentlyViewed, TrackRecentlyViewed } from "@/components/shop/recently-viewed";
import { ReviewsSection } from "@/components/shop/reviews-section";
import { StarRating } from "@/components/ui/star-rating";
import { api } from "@/lib/api";
import { getSettings } from "@/lib/data";
import { money, plural, trimNumber } from "@/lib/format";
import type { Paginated, Product, ProductDetail, Review, ReviewSummary } from "@/lib/types";

type ProductResponse = {
  product: ProductDetail;
  related: Product[];
  review_summary: ReviewSummary;
  my_review: Review | null;
  can_review: boolean;
};

const getProduct = cache(async (slug: string) => api<ProductResponse>(`/products/${encodeURIComponent(slug)}`));

export async function generateMetadata({ params }: PageProps<"/shop/[slug]">): Promise<Metadata> {
  const { slug } = await params;
  const res = await getProduct(slug);
  if (!res.ok) return { title: "Product not found" };
  const p = res.data.product;
  return {
    title: p.name,
    description: (p.description ?? "").slice(0, 160) || undefined,
    openGraph: { images: [p.image] },
  };
}

export default async function ProductPage({ params, searchParams }: PageProps<"/shop/[slug]">) {
  const { slug } = await params;
  const sp = await searchParams;
  const reviewsPage = Math.max(1, Number(Array.isArray(sp.reviews_page) ? sp.reviews_page[0] : sp.reviews_page) || 1);

  const [res, reviewsRes, settings] = await Promise.all([
    getProduct(slug),
    api<Paginated<Review>>(`/products/${encodeURIComponent(slug)}/reviews?page=${reviewsPage}`, { auth: false }),
    getSettings(),
  ]);

  if (!res.ok) {
    if (res.status === 404) notFound();
    throw new Error(res.message);
  }

  const { product, related, review_summary: summary, my_review, can_review } = res.data;
  const freeShipping = trimNumber(settings.free_shipping_threshold);

  const trust = [
    { Icon: Truck, title: "Free Shipping", sub: `On orders over $${freeShipping}` },
    { Icon: ShieldCheck, title: "Secure Payment", sub: "100% secure" },
    { Icon: RefreshCw, title: "Easy Returns", sub: "7 days return" },
    { Icon: Headphones, title: "24/7 Support", sub: "We're here to help" },
  ];

  const faqs: [string, string][] = [
    [
      "How much does shipping cost?",
      `Shipping is ${money(settings.shipping_fee)} per order, and free when your subtotal is $${freeShipping} or more.`,
    ],
    ["Can I return this product?", "Yes, returns are accepted within 7 days of delivery."],
    ["Which payment methods do you accept?", "Cash on delivery and credit / debit card, processed securely by Stripe."],
    ["Who can write a review?", "Any logged-in customer. Reviews from people who bought the product show a Verified buyer badge."],
  ];

  const description = product.description || "No description available.";

  return (
    <div className="mx-auto max-w-7xl px-4 py-8">
      <TrackRecentlyViewed product={product} />

      <nav className="mb-4 flex flex-wrap items-center gap-1.5 text-xs text-gray-500" aria-label="Breadcrumb">
        <Link href="/" className="hover:text-blue-600">Home</Link>
        <span aria-hidden>/</span>
        <Link href="/shop" className="hover:text-blue-600">Shop</Link>
        {product.category && (
          <>
            <span aria-hidden>/</span>
            <Link href={`/shop?category=${product.category.slug}`} className="hover:text-blue-600">{product.category.name}</Link>
          </>
        )}
        <span aria-hidden>/</span>
        <span className="text-gray-700">{product.name}</span>
      </nav>

      <div className="grid gap-8 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm md:p-8 lg:grid-cols-2 lg:gap-12">
        <ProductGallery images={product.gallery} name={product.name} inStock={product.stock > 0} />

        <div>
          {product.category && <p className="mb-1 text-sm font-medium text-blue-600">{product.category.name}</p>}
          <h1 className="mb-3 text-2xl font-bold text-slate-900 md:text-3xl">{product.name}</h1>

          <a href="#reviews" className="group mb-4 inline-flex items-center gap-2">
            <StarRating rating={summary.average} />
            <span className="text-sm text-gray-600 group-hover:text-blue-600">
              {summary.count ? `${summary.average.toFixed(1)} (${summary.count} ${plural("review", summary.count)})` : "No reviews yet"}
            </span>
          </a>

          <div className="mb-4 flex items-center gap-3">
            <span className="text-3xl font-bold text-blue-600">{money(product.final_price)}</span>
            {product.discount_price !== null && (
              <>
                <span className="text-gray-400 line-through">{money(product.price)}</span>
                <span className="rounded-md border border-red-200 bg-red-50 px-2 py-0.5 text-xs font-medium text-red-600">
                  {product.discount_percent}% OFF
                </span>
              </>
            )}
          </div>

          <p className="mb-5 leading-relaxed text-gray-600">
            {description.length > 220 ? `${description.slice(0, 217)}...` : description}
          </p>

          <BuyBox product={product} />
        </div>
      </div>

      <div className="mt-6 grid grid-cols-2 gap-4 rounded-2xl border border-blue-100 bg-blue-50/60 p-5 lg:grid-cols-4">
        {trust.map(({ Icon, title, sub }) => (
          <div key={title} className="flex items-center gap-3">
            <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white text-blue-600 shadow-sm">
              <Icon className="h-5 w-5" />
            </div>
            <div>
              <div className="text-sm font-semibold text-slate-900">{title}</div>
              <div className="text-xs text-gray-500">{sub}</div>
            </div>
          </div>
        ))}
      </div>

      <ProductTabs
        description={description}
        reviewCount={summary.count}
        freeShipping={freeShipping}
        faqs={faqs}
        specs={[
          ["Category", product.category?.name ?? "—"],
          ["SKU", product.sku],
          ["Availability", product.stock > 0 ? "In stock" : "Out of stock"],
          ["Price", money(product.final_price)],
        ]}
      />

      <ReviewsSection
        slug={product.slug}
        summary={summary}
        reviews={reviewsRes.ok ? reviewsRes.data : null}
        canReview={can_review}
        myReview={my_review}
      />

      {related.length > 0 && (
        <section className="mt-12">
          <h2 className="mb-5 text-xl font-bold text-slate-900">Related Products</h2>
          <ProductGrid products={related} className="grid grid-cols-2 gap-5 md:grid-cols-4" />
        </section>
      )}

      <div className="-mx-4">
        <RecentlyViewed excludeId={product.id} />
      </div>
    </div>
  );
}
