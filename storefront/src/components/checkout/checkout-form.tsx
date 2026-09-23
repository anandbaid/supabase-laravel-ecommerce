"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useState, useTransition } from "react";
import { ArrowLeft, Banknote, CreditCard, Lock, RefreshCw, ShieldCheck, ShoppingBag, Truck } from "lucide-react";
import { placeOrder, type CheckoutPayload } from "@/app/actions/account";
import { CouponForm } from "@/components/cart/coupon-form";
import { SummaryRows } from "@/components/cart/order-summary-rows";
import { useCartQuote } from "@/components/cart/use-quote";
import { useCart } from "@/components/providers/cart";
import { Alert, Field, FieldError, inputClass } from "@/components/ui/form";
import { money } from "@/lib/format";
import type { Address, User } from "@/lib/types";

const COUNTRIES = [
  "India", "United States", "United Kingdom", "Canada", "Australia", "Bangladesh",
  "Nepal", "Singapore", "United Arab Emirates", "Other",
];

type Props = {
  user: User;
  addresses: Address[];
  prefill: Address | null;
  freeShippingThreshold: number;
  paymentCancelled: boolean;
};

export function CheckoutForm({ user, addresses, prefill, freeShippingThreshold, paymentCancelled }: Props) {
  const router = useRouter();
  const cart = useCart();
  const { quote, error: quoteError, couponError, loading } = useCartQuote();
  const [sameAsBilling, setSameAsBilling] = useState(true);
  const [addressId, setAddressId] = useState<string>(addresses.length ? String(prefill?.id ?? addresses[0].id) : "");
  const [notes, setNotes] = useState("");
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [formError, setFormError] = useState<string | null>(null);
  const [cartChanged, setCartChanged] = useState(false);
  const [pending, start] = useTransition();
  const [redirecting, setRedirecting] = useState(false);

  // Returning from Stripe via the Back button restores this page from cache.
  useEffect(() => {
    const onShow = (e: PageTransitionEvent) => e.persisted && setRedirecting(false);
    window.addEventListener("pageshow", onShow);
    return () => window.removeEventListener("pageshow", onShow);
  }, []);

  const [firstName, ...rest] = (user.name ?? "").trim().split(" ");
  const lastName = rest.join(" ");
  const useNewShipping = !sameAsBilling && (addresses.length === 0 || addressId === "");

  const submit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const form = e.currentTarget;
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }
    if (!quote || quote.items.length === 0) return;

    const data = new FormData(form);
    const payload: CheckoutPayload = {
      items: quote.items.map((l) => ({ product_id: l.product.id, qty: l.qty })),
      coupon_code: quote.coupon?.code ?? null,
      same_as_billing: sameAsBilling,
      address_id: !sameAsBilling && addressId ? Number(addressId) : null,
    };
    for (const [k, v] of data.entries()) {
      if (typeof v === "string" && !(k in payload)) payload[k] = v;
    }

    start(async () => {
      setFormError(null);
      setCartChanged(false);
      const res = await placeOrder(payload);

      if (!res.ok) {
        if (res.status === 401) {
          router.push("/login?next=/checkout");
          return;
        }
        setErrors(res.errors ?? {});
        if (res.status === 409) setCartChanged(true);
        if (res.errors?.coupon_code) cart.setCoupon(null);
        setFormError(res.errors && res.status === 422 && !res.errors.coupon_code ? "Please check the highlighted fields." : res.message);
        window.scrollTo({ top: 0, behavior: "smooth" });
        return;
      }

      setRedirecting(true);
      cart.clear();
      if (res.data.checkout_url) {
        window.location.assign(res.data.checkout_url);
      } else {
        router.push(`/checkout/success/${res.data.order_number}`);
      }
    });
  };

  if (cart.hydrated && cart.count === 0 && !redirecting && !pending) {
    return (
      <div className="mx-auto max-w-7xl px-4 py-8">
        {paymentCancelled && (
          <div className="mb-6">
            <Alert tone="info">
              Card payment was cancelled. Your order was saved as unpaid — you can find it in{" "}
              <Link href="/account/orders" className="font-medium underline">My Orders</Link>.
            </Alert>
          </div>
        )}
        <div className="rounded-2xl border border-gray-100 bg-white p-12 text-center shadow-sm">
          <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-500">
            <ShoppingBag className="h-7 w-7" />
          </div>
          <h2 className="mb-1 font-semibold text-slate-900">Your cart is empty</h2>
          <p className="mb-5 text-sm text-gray-500">Add something to your cart before checking out.</p>
          <Link href="/shop" className="inline-block rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
            Browse the shop
          </Link>
        </div>
      </div>
    );
  }

  const busy = pending || redirecting;

  return (
    <div className="mx-auto max-w-7xl px-4 py-8">
      <div className="mb-2 flex items-center gap-3">
        <Link href="/cart" aria-label="Back to cart" className="flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 hover:text-blue-600">
          <ArrowLeft className="h-4 w-4" />
        </Link>
        <h1 className="text-2xl font-bold text-slate-900">Checkout</h1>
      </div>
      <p className="mb-6 ml-12 text-sm text-gray-500">Complete your order and get your products delivered!</p>

      <div className="mb-6 space-y-3">
        {paymentCancelled && (
          <Alert tone="info">
            Card payment was cancelled, so nothing was charged. Your earlier order is saved as unpaid in{" "}
            <Link href="/account/orders" className="font-medium underline">My Orders</Link>.
          </Alert>
        )}
        {quoteError && <Alert tone="error">{quoteError}</Alert>}
        {formError && (
          <Alert tone="error">
            {formError}
            {cartChanged && (
              <>
                {" "}
                <Link href="/cart" className="font-medium underline">Review your cart</Link>
              </>
            )}
          </Alert>
        )}
      </div>

      <div className="grid items-start gap-6 lg:grid-cols-[1fr_400px]">
        <form id="checkout-form" onSubmit={submit} noValidate className="min-w-0 space-y-5">
          <section className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <SectionTitle n={1}>Billing Details</SectionTitle>
            <div className="grid gap-4 sm:grid-cols-2">
              <Field name="first_name" label="First Name" defaultValue={firstName} required autoComplete="given-name" errors={errors} />
              <Field name="last_name" label="Last Name" defaultValue={lastName} required autoComplete="family-name" errors={errors} />
              <Field name="customer_email" label="Email Address" type="email" defaultValue={user.email} required autoComplete="email" errors={errors} />
              <Field name="customer_phone" label="Phone Number" type="tel" defaultValue={prefill?.phone ?? user.phone ?? ""} placeholder="+91 98765 43210" required autoComplete="tel" errors={errors} />
              <Field name="company_name" label="Company Name" optional placeholder="Enter company name" wrapperClassName="sm:col-span-2" autoComplete="organization" errors={errors} />
              <CountrySelect name="billing_country" defaultValue={prefill?.country ?? "India"} errors={errors} required />
              <Field name="billing_line1" label="Street Address" defaultValue={prefill?.line1 ?? ""} required autoComplete="address-line1" errors={errors} />
              <Field name="billing_line2" label="Apartment, Suite, etc." defaultValue={prefill?.line2 ?? ""} optional placeholder="Flat, floor, building (optional)" wrapperClassName="sm:col-span-2" autoComplete="address-line2" errors={errors} />
              <Field name="billing_city" label="City" defaultValue={prefill?.city ?? ""} required autoComplete="address-level2" errors={errors} />
              <div className="grid grid-cols-2 gap-4">
                <Field name="billing_state" label="State" defaultValue={prefill?.state ?? ""} required autoComplete="address-level1" errors={errors} />
                <Field name="billing_postal_code" label="ZIP Code" defaultValue={prefill?.postal_code ?? ""} required autoComplete="postal-code" errors={errors} />
              </div>
            </div>
          </section>

          <section className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <SectionTitle n={2}>Shipping Address</SectionTitle>
            <label className="flex w-fit cursor-pointer items-center gap-2 text-sm text-gray-700">
              <input type="checkbox" checked={sameAsBilling} onChange={(e) => setSameAsBilling(e.target.checked)} className="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
              Same as billing address
            </label>

            {!sameAsBilling && (
              <div className="mt-5 space-y-4">
                {addresses.length > 0 && (
                  <div className="space-y-2">
                    {addresses.map((a) => (
                      <label key={a.id} className="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-3 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" checked={addressId === String(a.id)} onChange={() => setAddressId(String(a.id))} className="mt-1 text-blue-600 focus:ring-blue-500" />
                        <div className="text-sm">
                          <span className="font-semibold">{a.label}</span>
                          {a.is_default && <span className="ml-1 text-xs text-blue-600">(Default)</span>}
                          <div className="text-gray-600">
                            {[a.full_name, a.line1, a.line2, a.city, `${a.state} ${a.postal_code}`, a.country].filter(Boolean).join(", ")}
                          </div>
                          <div className="text-gray-400">Phone: {a.phone}</div>
                        </div>
                      </label>
                    ))}
                    <label className="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 p-3 text-sm has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                      <input type="radio" checked={addressId === ""} onChange={() => setAddressId("")} className="text-blue-600 focus:ring-blue-500" />
                      Ship to a different address
                    </label>
                  </div>
                )}

                {useNewShipping && (
                  <div className="grid gap-4 sm:grid-cols-2">
                    <Field name="shipping_first_name" label="First Name" defaultValue={firstName} required errors={errors} />
                    <Field name="shipping_last_name" label="Last Name" defaultValue={lastName} required errors={errors} />
                    <Field name="shipping_phone" label="Phone Number" type="tel" required errors={errors} />
                    <CountrySelect name="shipping_country" defaultValue="India" errors={errors} required />
                    <Field name="shipping_line1" label="Street Address" required wrapperClassName="sm:col-span-2" errors={errors} />
                    <Field name="shipping_line2" label="Apartment, Suite, etc." optional placeholder="Flat, floor, building (optional)" wrapperClassName="sm:col-span-2" errors={errors} />
                    <Field name="shipping_city" label="City" required errors={errors} />
                    <div className="grid grid-cols-2 gap-4">
                      <Field name="shipping_state" label="State" required errors={errors} />
                      <Field name="shipping_postal_code" label="ZIP Code" required errors={errors} />
                    </div>
                  </div>
                )}
              </div>
            )}
          </section>

          <section className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <SectionTitle n={3}>
              Order Notes <span className="text-sm font-normal text-gray-400">(Optional)</span>
            </SectionTitle>
            <textarea
              name="notes"
              rows={3}
              maxLength={500}
              value={notes}
              onChange={(e) => setNotes(e.target.value)}
              placeholder="Special delivery instructions, gift message, etc."
              aria-label="Order notes"
              className={inputClass}
            />
            <div className="mt-1 text-right text-xs text-gray-400">{notes.length}/500</div>
          </section>

          <section className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <SectionTitle n={4}>Payment Method</SectionTitle>
            <div className="space-y-3">
              <label className="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 p-4 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                <input type="radio" name="payment_method" value="cod" defaultChecked className="text-blue-600 focus:ring-blue-500" />
                <div className="flex-1">
                  <div className="text-sm font-medium text-slate-900">Cash on Delivery</div>
                  <div className="text-xs text-gray-500">Pay when your order arrives at your doorstep.</div>
                </div>
                <Banknote className="h-6 w-6 text-blue-600" />
              </label>
              <label className="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 p-4 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                <input type="radio" name="payment_method" value="card" className="text-blue-600 focus:ring-blue-500" />
                <div className="flex-1">
                  <div className="text-sm font-medium text-slate-900">
                    Credit / Debit Card <span className="font-normal text-gray-400">(via Stripe)</span>
                  </div>
                  <div className="text-xs text-gray-500">You&apos;ll be taken to Stripe&apos;s secure page to complete payment.</div>
                </div>
                <CreditCard className="h-6 w-6 text-blue-600" />
              </label>
              <FieldError errors={errors} name="payment_method" />
            </div>
          </section>
        </form>

        <aside className="space-y-5 lg:sticky lg:top-24">
          <div className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div className="mb-4 flex items-center justify-between">
              <h2 className="font-bold text-slate-900">Order Summary</h2>
              <Link href="/cart" className="text-xs text-blue-600 hover:underline">Edit cart</Link>
            </div>
            {!quote ? (
              <p className="py-6 text-center text-sm text-gray-400">Loading your order…</p>
            ) : (
              <div className={loading ? "opacity-70 transition-opacity" : ""}>
                <ul className="mb-4 space-y-3">
                  {quote.items.map(({ product: p, qty, subtotal }) => (
                    <li key={p.id} className="flex items-center gap-3">
                      {/* eslint-disable-next-line @next/next/no-img-element */}
                      <img src={p.image_small} alt="" className="h-14 w-14 shrink-0 rounded-lg bg-gray-50 object-contain p-1" />
                      <div className="min-w-0 flex-1">
                        <div className="truncate text-sm font-medium text-slate-900">{p.name}</div>
                        <div className="text-xs text-gray-500">Qty: {qty}</div>
                      </div>
                      <div className="text-sm font-semibold">{money(subtotal)}</div>
                    </li>
                  ))}
                </ul>
                <div className="border-t border-gray-100 pt-4">
                  <SummaryRows quote={quote} subtotalLabel="Product Total" />
                </div>
                <div className="mt-4 flex items-baseline justify-between border-t border-gray-100 pt-4">
                  <span className="font-bold text-slate-900">Grand Total</span>
                  <span className="text-2xl font-bold text-blue-600">{money(quote.total)}</span>
                </div>
                <div className="mt-4">
                  <CouponForm quote={quote} couponError={couponError ?? errors.coupon_code?.[0] ?? null} compact />
                </div>
              </div>
            )}
            <button
              type="submit"
              form="checkout-form"
              disabled={busy || loading || !quote || quote.items.length === 0}
              className="mt-5 flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 py-3.5 font-semibold text-white hover:bg-blue-700 disabled:opacity-70"
            >
              <Lock className="h-4 w-4" /> {busy ? "Placing order…" : "Place Order"}
            </button>
            <p className="mt-3 text-center text-xs text-gray-400">By placing this order, you confirm the details above are correct.</p>
          </div>

          <div className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h3 className="mb-3 font-semibold text-slate-900">Your order includes</h3>
            <ul className="space-y-3 text-sm">
              {[
                { Icon: ShieldCheck, title: "Secure Checkout", sub: "Your information is safe with us" },
                { Icon: RefreshCw, title: "Easy Returns", sub: "Hassle-free returns within 7 days" },
                { Icon: Truck, title: "Fast Delivery", sub: `Free shipping over ${money(freeShippingThreshold)}` },
              ].map(({ Icon, title, sub }) => (
                <li key={title} className="flex items-start gap-3">
                  <Icon className="h-5 w-5 shrink-0 text-blue-600" />
                  <span>
                    <span className="block font-medium text-slate-900">{title}</span>
                    <span className="text-xs text-gray-500">{sub}</span>
                  </span>
                </li>
              ))}
            </ul>
          </div>
        </aside>
      </div>
    </div>
  );
}

function SectionTitle({ n, children }: { n: number; children: React.ReactNode }) {
  return (
    <h2 className="mb-5 flex items-center gap-3 font-bold text-slate-900">
      <span className="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs text-white">{n}</span>
      {children}
    </h2>
  );
}

function CountrySelect({
  name,
  defaultValue,
  errors,
  required,
}: {
  name: string;
  defaultValue: string;
  errors: Record<string, string[]>;
  required?: boolean;
}) {
  const value = COUNTRIES.includes(defaultValue) ? defaultValue : "Other";
  return (
    <div>
      <label htmlFor={name} className="mb-1 block text-sm font-medium text-gray-700">
        Country {required && <span className="text-red-500" aria-hidden>*</span>}
      </label>
      <select id={name} name={name} defaultValue={value} required={required} className={inputClass}>
        {COUNTRIES.map((c) => (
          <option key={c} value={c}>{c}</option>
        ))}
      </select>
      <FieldError errors={errors} name={name} />
    </div>
  );
}
