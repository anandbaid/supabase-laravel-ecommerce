<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Order */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'payment_status_label' => $this->paymentStatusLabel(),
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'company_name' => $this->company_name,
            'billing_address' => $this->billing_address,
            'shipping_address' => $this->shipping_address,
            'notes' => $this->notes,
            'subtotal' => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'coupon_code' => $this->coupon_code,
            'tax_rate' => (float) $this->tax_rate,
            'tax_amount' => (float) $this->tax_amount,
            'shipping_amount' => (float) $this->shipping_amount,
            'total' => (float) $this->total,
            'created_at' => $this->created_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'cancellation_reason' => $this->cancellation_reason,
            'return_status' => $this->return_status,
            'return_status_label' => $this->returnStatusLabel(),
            'return_reason' => $this->return_reason,
            'return_requested_at' => $this->return_requested_at?->toIso8601String(),
            'return_window_expires_at' => $this->returnWindowExpiresAt()?->toIso8601String(),
            'refund_amount' => $this->refund_amount !== null ? (float) $this->refund_amount : null,
            'refunded_at' => $this->refunded_at?->toIso8601String(),
            'can_cancel' => $this->canBeCancelled(),
            'can_request_return' => $this->canRequestReturn(),
            'items_count' => $this->items_count ?? $this->whenLoaded('items', fn () => $this->items->count()),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'product_slug' => $item->relationLoaded('product') ? $item->product?->slug : null,
                'image' => $item->relationLoaded('product') && $item->product ? $item->product->imageUrlSmall() : null,
                'price' => (float) $item->price,
                'quantity' => (int) $item->quantity,
                'subtotal' => (float) $item->subtotal,
            ])->values()),
        ];
    }
}
