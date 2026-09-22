<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'supabase_uid',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class)->orderByDesc('is_default');
    }

    public function defaultAddress()
    {
        return $this->addresses()->where('is_default', true)->first() ?? $this->addresses()->first();
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    /** @var \Illuminate\Support\Collection<int, int>|null */
    protected ?\Illuminate\Support\Collection $wishlistIdsCache = null;

    /** IDs of products this user has saved, memoised for the request. */
    public function wishlistProductIds(): \Illuminate\Support\Collection
    {
        return $this->wishlistIdsCache ??= $this->wishlists()->pluck('product_id');
    }
}
