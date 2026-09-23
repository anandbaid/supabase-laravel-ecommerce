"use client";

import { useState } from "react";
import { ChevronDown, Lock, ShieldCheck, Truck } from "lucide-react";

type Tab = "description" | "specs" | "faq";

export function ProductTabs({
  description,
  specs,
  faqs,
  reviewCount,
  freeShipping,
}: {
  description: string;
  specs: [string, string][];
  faqs: [string, string][];
  reviewCount: number;
  freeShipping: string;
}) {
  const [tab, setTab] = useState<Tab>("description");
  const tabClass = (t: Tab) =>
    `whitespace-nowrap border-b-2 py-4 text-sm font-medium ${
      tab === t ? "border-blue-600 text-blue-600" : "border-transparent text-gray-500 hover:text-gray-800"
    }`;

  return (
    <div className="mt-8 rounded-2xl border border-gray-100 bg-white shadow-sm">
      <div className="flex gap-6 overflow-x-auto border-b border-gray-100 px-6" role="tablist">
        <button type="button" role="tab" aria-selected={tab === "description"} onClick={() => setTab("description")} className={tabClass("description")}>
          Description
        </button>
        <button type="button" role="tab" aria-selected={tab === "specs"} onClick={() => setTab("specs")} className={tabClass("specs")}>
          Specifications
        </button>
        <a href="#reviews" className="whitespace-nowrap border-b-2 border-transparent py-4 text-sm font-medium text-gray-500 hover:text-gray-800">
          Reviews ({reviewCount})
        </a>
        <button type="button" role="tab" aria-selected={tab === "faq"} onClick={() => setTab("faq")} className={tabClass("faq")}>
          FAQ
        </button>
      </div>

      <div className="p-6">
        {tab === "description" && (
          <div role="tabpanel" className="grid gap-8 md:grid-cols-[1fr_320px]">
            <div>
              <h2 className="mb-3 text-lg font-bold text-slate-900">Product Description</h2>
              <p className="whitespace-pre-line leading-relaxed text-gray-600">{description}</p>
            </div>
            <div className="h-fit rounded-xl bg-slate-50 p-5">
              <h3 className="mb-3 font-semibold text-slate-900">Why choose us?</h3>
              <ul className="space-y-3 text-sm">
                {[
                  { Icon: ShieldCheck, title: "Quality products", sub: "Carefully selected for you" },
                  { Icon: Lock, title: "Safe & secure payment", sub: "Cash on delivery or card" },
                  { Icon: Truck, title: "Fast & reliable delivery", sub: `Free above $${freeShipping}` },
                ].map(({ Icon, title, sub }) => (
                  <li key={title} className="flex gap-3">
                    <Icon className="h-5 w-5 shrink-0 text-blue-600" />
                    <span>
                      <span className="block font-medium text-slate-900">{title}</span>
                      <span className="text-xs text-gray-500">{sub}</span>
                    </span>
                  </li>
                ))}
              </ul>
            </div>
          </div>
        )}

        {tab === "specs" && (
          <div role="tabpanel">
            <h2 className="mb-3 text-lg font-bold text-slate-900">Specifications</h2>
            <dl className="max-w-xl divide-y divide-gray-100 text-sm">
              {specs.map(([k, v]) => (
                <div key={k} className="flex justify-between py-2.5">
                  <dt className="text-gray-500">{k}</dt>
                  <dd className="font-medium">{v}</dd>
                </div>
              ))}
            </dl>
          </div>
        )}

        {tab === "faq" && (
          <div role="tabpanel" className="max-w-2xl space-y-3">
            <h2 className="mb-1 text-lg font-bold text-slate-900">Frequently asked questions</h2>
            {faqs.map(([q, a]) => (
              <details key={q} className="group rounded-lg border border-gray-200 px-4 py-3">
                <summary className="flex cursor-pointer list-none items-center justify-between text-sm font-medium text-slate-900">
                  {q} <ChevronDown className="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" />
                </summary>
                <p className="mt-2 text-sm text-gray-600">{a}</p>
              </details>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
