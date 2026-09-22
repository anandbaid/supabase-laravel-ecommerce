@extends('layouts.admin')
@section('page-title', 'Order #' . $order->order_number)

@section('content')
<div class="grid md:grid-cols-3 gap-6">
    <div class="md:col-span-2 bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold mb-4">Order Items</h3>
        <table class="w-full text-sm">
            <thead class="text-gray-400 text-left border-b">
                <tr><th class="pb-2">Product</th><th class="pb-2">Price</th><th class="pb-2">Qty</th><th class="pb-2 text-right">Subtotal</th></tr>
            </thead>
            <tbody class="divide-y">
                @foreach($order->items as $item)
                    <tr>
                        <td class="py-3">{{ $item->product_name }}</td>
                        <td class="py-3">${{ number_format($item->price, 2) }}</td>
                        <td class="py-3">{{ $item->quantity }}</td>
                        <td class="py-3 text-right font-medium">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="flex justify-end border-t mt-4 pt-4">
            <div class="text-right">
                @if($order->shipping_amount > 0)
                    <div class="text-gray-500 text-sm">Shipping: ${{ number_format($order->shipping_amount, 2) }}</div>
                @endif
                <div class="text-gray-500 text-sm">Total</div>
                <div class="text-xl font-bold text-blue-600">${{ number_format($order->total, 2) }}</div>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t">
            <h3 class="font-semibold mb-3">Customer & Shipping</h3>
            <p class="text-sm"><span class="text-gray-500">Name:</span> {{ $order->customer_name }}</p>
            <p class="text-sm"><span class="text-gray-500">Email:</span> {{ $order->customer_email }}</p>
            <p class="text-sm"><span class="text-gray-500">Phone:</span> {{ $order->customer_phone ?: '—' }}</p>
            @if($order->company_name)
                <p class="text-sm"><span class="text-gray-500">Company:</span> {{ $order->company_name }}</p>
            @endif
            <p class="text-sm mt-2"><span class="text-gray-500">Shipping address:</span></p>
            <p class="text-sm whitespace-pre-line">{{ $order->shipping_address }}</p>
            @if($order->billing_address && $order->billing_address !== $order->shipping_address)
                <p class="text-sm mt-2"><span class="text-gray-500">Billing address:</span></p>
                <p class="text-sm whitespace-pre-line">{{ $order->billing_address }}</p>
            @endif
            @if($order->notes)
                <p class="text-sm mt-3"><span class="text-gray-500">Order notes:</span></p>
                <p class="text-sm whitespace-pre-line bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2 mt-1">{{ $order->notes }}</p>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">{{ session('error') }}</div>
        @endif

        @if($order->return_status)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold">Return Request</h3>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $order->returnStatusColor() }}">{{ $order->returnStatusLabel() }}</span>
                </div>
                <p class="text-sm text-gray-600 whitespace-pre-line mb-1">{{ $order->return_reason }}</p>
                <p class="text-xs text-gray-400">Requested {{ $order->return_requested_at?->format('d M Y, g:i A') }}</p>

                @if($order->return_status === 'requested')
                    <div class="flex gap-2 mt-4">
                        <form action="{{ route('admin.orders.return.approve', $order) }}" method="POST" class="flex-1">
                            @csrf
                            <button class="w-full bg-blue-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-blue-700">Approve</button>
                        </form>
                        <form action="{{ route('admin.orders.return.reject', $order) }}" method="POST" class="flex-1">
                            @csrf
                            <button class="w-full border border-red-200 text-red-600 py-2 rounded-lg text-sm font-medium hover:bg-red-50">Reject</button>
                        </form>
                    </div>
                @elseif($order->return_status === 'approved' && $order->payment_status !== 'refunded')
                    <form action="{{ route('admin.orders.refund', $order) }}" method="POST" class="mt-4" onsubmit="return confirm('Process a full refund of ${{ number_format($order->total, 2) }}?')">
                        @csrf
                        <button class="w-full bg-purple-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-purple-700">
                            Process Refund {{ $order->isRefundableViaStripe() ? '(via Stripe)' : '(mark as refunded)' }}
                        </button>
                    </form>
                @endif
            </div>
        @endif

        @if($order->cancelledByCustomer())
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold mb-2">Cancelled by Customer</h3>
                <p class="text-sm text-gray-600">On {{ $order->cancelled_at->format('d M Y, g:i A') }}</p>
                @if($order->cancellation_reason)
                    <p class="text-sm text-gray-600 mt-1">"{{ $order->cancellation_reason }}"</p>
                @endif
                @if($order->payment_status === 'refunded')
                    <p class="text-xs text-purple-600 mt-2">Refunded {{ $order->refunded_at?->format('d M Y') }}{{ $order->stripe_refund_id ? ' via Stripe' : '' }}.</p>
                @elseif($order->payment_method === 'card' && $order->payment_status !== 'refunded')
                    <form action="{{ route('admin.orders.refund', $order) }}" method="POST" class="mt-3" onsubmit="return confirm('Process a full refund of ${{ number_format($order->total, 2) }}?')">
                        @csrf
                        <button class="w-full bg-purple-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-purple-700">Process Refund</button>
                    </form>
                @endif
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold mb-4">Update Order</h3>
            <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="space-y-4">
                @csrf @method('PATCH')
                <div>
                    <label class="text-sm font-medium">Order Status</label>
                    <select name="status" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
                        @foreach(['pending','processing','shipped','delivered','cancelled'] as $s)
                            <option value="{{ $s }}" @selected($order->status == $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    @if($order->delivered_at)
                        <p class="text-xs text-gray-400 mt-1">Delivered {{ $order->delivered_at->format('d M Y') }} — return window {{ now()->gt($order->returnWindowExpiresAt()) ? 'closed' : 'open until ' . $order->returnWindowExpiresAt()->format('d M Y') }}.</p>
                    @endif
                </div>
                <div>
                    <label class="text-sm font-medium">Payment Status</label>
                    <select name="payment_status" class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
                        <option value="unpaid" @selected($order->payment_status == 'unpaid')>Unpaid</option>
                        <option value="paid" @selected($order->payment_status == 'paid')>Paid</option>
                        <option value="failed" @selected($order->payment_status == 'failed')>Failed</option>
                        <option value="refunded" @selected($order->payment_status == 'refunded')>Refunded</option>
                    </select>
                </div>
                <button class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium">Update Order</button>
            </form>
        </div>
    </div>
</div>
@endsection
