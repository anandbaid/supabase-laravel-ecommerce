<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            $row = static::where('key', $key)->first();
            return $row ? $row->value : $default;
        });
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting:{$key}");
    }

    /** Current store-wide tax rate as a percentage, e.g. 8.5 for 8.5%. */
    public static function taxRate(): float
    {
        return (float) static::get('tax_rate', 0);
    }

    /** Flat shipping fee charged when the order is below the free-shipping threshold. */
    public static function shippingFee(): float
    {
        return (float) static::get('shipping_fee', 2);
    }

    /** Order subtotal at (or above) which shipping is free. */
    public static function freeShippingThreshold(): float
    {
        return (float) static::get('free_shipping_threshold', 50);
    }
}
