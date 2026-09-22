@component('mail::message')
# Order Update

Your order **#{{ $order->order_number }}** status changed from **{{ ucfirst($previousStatus) }}** to **{{ ucfirst($order->status) }}**.

@if($order->status === 'shipped')
Your order is on its way!
@elseif($order->status === 'delivered')
Your order has been delivered. If anything's not right, you have {{ \App\Models\Order::RETURN_WINDOW_DAYS }} days to request a return from your order page.
@elseif($order->status === 'cancelled')
This order has been cancelled. If you weren't expecting this, please contact us.
@endif

@component('mail::button', ['url' => route('account.orders.show', $order->order_number)])
View Order
@endcomponent

Thanks,
{{ config('app.name') }}
@endcomponent
