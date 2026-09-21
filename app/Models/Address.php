<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'label', 'full_name', 'phone', 'line1', 'line2',
        'city', 'state', 'postal_code', 'country', 'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function formatted(): string
    {
        $lines = array_filter([
            $this->full_name,
            $this->line1,
            $this->line2,
            trim($this->city . ', ' . $this->state . ' ' . $this->postal_code, ', '),
            $this->country,
            'Phone: ' . $this->phone,
        ]);

        return implode("\n", $lines);
    }
}
