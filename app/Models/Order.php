<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'customer_name', 'customer_email', 'customer_phone',
        'shipping_address', 'address_id', 'subtotal', 'tax_rate', 'tax_amount',
        'coupon_code', 'discount_amount', 'shipping_amount', 'company_name', 'billing_address', 'notes', 'total', 'status', 'payment_method', 'payment_status',
        'stripe_checkout_session_id', 'stripe_payment_intent_id',
        'delivered_at', 'cancelled_at', 'cancellation_reason',
        'return_status', 'return_reason', 'return_requested_at', 'return_decided_at',
        'refund_amount', 'refunded_at', 'stripe_refund_id',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'return_requested_at' => 'datetime',
        'return_decided_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(8));
            }
        });
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'delivered' => 'bg-green-100 text-green-700',
            'processing' => 'bg-blue-100 text-blue-700',
            'pending' => 'bg-yellow-100 text-yellow-700',
            'shipped' => 'bg-indigo-100 text-indigo-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'paid' => 'Paid',
            'failed' => 'Failed',
            default => 'Payment pending',
        };
    }

    public function paymentStatusColor(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-green-100 text-green-700',
            'failed' => 'bg-red-100 text-red-700',
            'refunded' => 'bg-purple-100 text-purple-700',
            default => 'bg-yellow-100 text-yellow-700',
        };
    }

    /**
     * The customer can cancel any time before the order ships — once it's
     * marked shipped/delivered/cancelled (or already has a return in play),
     * cancellation is no longer offered from the client side.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing'], true);
    }

    public function cancelledByCustomer(): bool
    {
        return $this->status === 'cancelled' && !is_null($this->cancelled_at);
    }

    /** Standard 7-day return window, counted from delivery. */
    public const RETURN_WINDOW_DAYS = 7;

    public function returnWindowExpiresAt(): ?\Carbon\Carbon
    {
        return $this->delivered_at?->copy()->addDays(self::RETURN_WINDOW_DAYS);
    }

    public function canRequestReturn(): bool
    {
        if ($this->status !== 'delivered' || !$this->delivered_at || !empty($this->return_status)) {
            return false;
        }

        return now()->lte($this->returnWindowExpiresAt());
    }

    public function hasActiveReturn(): bool
    {
        return in_array($this->return_status, ['requested', 'approved'], true);
    }

    public function returnStatusLabel(): string
    {
        return match ($this->return_status) {
            'requested' => 'Return requested',
            'approved' => 'Return approved',
            'rejected' => 'Return rejected',
            'refunded' => 'Refunded',
            default => '',
        };
    }

    public function returnStatusColor(): string
    {
        return match ($this->return_status) {
            'requested' => 'bg-yellow-100 text-yellow-700',
            'approved' => 'bg-blue-100 text-blue-700',
            'rejected' => 'bg-red-100 text-red-700',
            'refunded' => 'bg-purple-100 text-purple-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    /** Whether this order was paid online (Stripe) and can be refunded via the Stripe API. */
    public function isRefundableViaStripe(): bool
    {
        return $this->payment_method === 'card'
            && $this->payment_status === 'paid'
            && !empty($this->stripe_payment_intent_id);
    }
}
