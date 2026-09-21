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
        'coupon_code', 'discount_amount', 'total', 'status', 'payment_method', 'payment_status',
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
}
