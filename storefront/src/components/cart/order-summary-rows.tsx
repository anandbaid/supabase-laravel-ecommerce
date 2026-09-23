import { money, trimNumber } from "@/lib/format";
import type { CartQuote } from "@/lib/types";

export function SummaryRows({ quote, subtotalLabel }: { quote: CartQuote; subtotalLabel: string }) {
  return (
    <dl className="space-y-3 text-sm">
      <div className="flex justify-between">
        <dt className="text-gray-500">{subtotalLabel}</dt>
        <dd className="font-medium">{money(quote.subtotal)}</dd>
      </div>
      {quote.coupon && (
        <div className="flex justify-between text-green-600">
          <dt>Discount ({quote.coupon.code})</dt>
          <dd className="font-medium">-{money(quote.discount)}</dd>
        </div>
      )}
      <div className="flex justify-between">
        <dt className="text-gray-500">Shipping</dt>
        <dd className="font-medium">{quote.shipping > 0 ? money(quote.shipping) : "Free"}</dd>
      </div>
      {quote.tax_rate > 0 && (
        <div className="flex justify-between">
          <dt className="text-gray-500">Tax ({trimNumber(quote.tax_rate)}%)</dt>
          <dd className="font-medium">{money(quote.tax_amount)}</dd>
        </div>
      )}
    </dl>
  );
}
