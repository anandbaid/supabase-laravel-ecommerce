import { money } from "@/lib/format";
import type { Order } from "@/lib/types";

export function OrderTotals({ order }: { order: Order }) {
  return (
    <>
      <div className="space-y-3">
        {order.items?.map((item) => (
          <div key={item.id} className="flex justify-between text-sm">
            <span>
              {item.product_name} <span className="text-gray-400">&times; {item.quantity}</span>
            </span>
            <span className="font-medium">{money(item.subtotal)}</span>
          </div>
        ))}
      </div>
      <div className="mt-4 space-y-2 border-t border-gray-100 pt-4 text-sm">
        <div className="flex justify-between"><span className="text-gray-500">Subtotal</span><span>{money(order.subtotal || order.total)}</span></div>
        {order.coupon_code && (
          <div className="flex justify-between text-green-600"><span>Discount ({order.coupon_code})</span><span>-{money(order.discount_amount)}</span></div>
        )}
        <div className="flex justify-between">
          <span className="text-gray-500">Shipping</span>
          <span>{order.shipping_amount > 0 ? money(order.shipping_amount) : "Free"}</span>
        </div>
        {order.tax_amount > 0 && (
          <div className="flex justify-between"><span className="text-gray-500">Tax</span><span>{money(order.tax_amount)}</span></div>
        )}
      </div>
      <div className="mt-2 flex justify-between border-t border-gray-100 pt-4 font-bold">
        <span>Total</span>
        <span className="text-blue-600">{money(order.total)}</span>
      </div>
    </>
  );
}
