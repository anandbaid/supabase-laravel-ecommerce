@component('mail::message')
# Thanks for your order, {{ explode(' ', $order->customer_name)[0] }}!

Your order **#{{ $order->order_number }}** has been confirmed.

@component('mail::table')
| Item | Qty | Price |
| :--- | :-: | ----: |
@foreach($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | ${{ number_format($item->subtotal, 2) }} |
@endforeach
@endcomponent

**Subtotal:** ${{ number_format($order->subtotal, 2) }}
@if($order->discount_amount > 0)
**Discount ({{ $order->coupon_code }}):** -${{ number_format($order->discount_amount, 2) }}
@endif
**Tax:** ${{ number_format($order->tax_amount, 2) }}
**Shipping:** {{ $order->shipping_amount > 0 ? '$' . number_format($order->shipping_amount, 2) : 'Free' }}
**Total: ${{ number_format($order->total, 2) }}**

Payment method: {{ $order->payment_method === 'card' ? 'Card' : 'Cash on Delivery' }}

**Shipping to:**
{{ $order->shipping_address }}

@component('mail::button', ['url' => route('account.orders.show', $order->order_number)])
Track Your Order
@endcomponent

Thanks for shopping with us!
{{ config('app.name') }}
@endcomponent
