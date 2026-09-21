<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'min_order_amount', 'max_discount_amount',
        'usage_limit', 'used_count', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($coupon) {
            if (!empty($coupon->code)) {
                $coupon->code = strtoupper($coupon->code);
            }
        });
    }

    /**
     * Validate this coupon against an order subtotal.
     * Returns null when valid, or an error message string when not.
     */
    public function validationError(float $subtotal): ?string
    {
        if (!$this->is_active) {
            return 'This coupon is no longer active.';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'This coupon has expired.';
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return 'This coupon has reached its usage limit.';
        }

        if ($subtotal < (float) $this->min_order_amount) {
            return 'Add items worth at least $' . number_format((float) $this->min_order_amount, 2) . ' to use this coupon.';
        }

        return null;
    }

    public function isValidFor(float $subtotal): bool
    {
        return $this->validationError($subtotal) === null;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'fixed') {
            $discount = (float) $this->value;
        } else {
            $discount = $subtotal * ((float) $this->value / 100);
            if ($this->max_discount_amount !== null) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }
        }

        return round(min($discount, $subtotal), 2);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
