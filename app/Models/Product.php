<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'price', 'discount_price',
        'stock', 'image', 'image_small', 'image_medium', 'image_large',
        'gallery', 'sku', 'is_featured', 'is_active',
    ];

    protected $casts = [
        'gallery' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name) . '-' . Str::random(5);
            }
            if (empty($product->sku)) {
                $product->sku = 'SKU-' . strtoupper(Str::random(8));
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /** @var array<string, mixed>|null */
    protected ?array $reviewSummaryCache = null;

    /**
     * Rating summary for this product, computed with a single grouped query
     * and memoised for the request.
     *
     * @return array{average: float, count: int, breakdown: array<int, array{count: int, percent: int}>}
     */
    public function reviewSummary(): array
    {
        if ($this->reviewSummaryCache !== null) {
            return $this->reviewSummaryCache;
        }

        $counts = $this->reviews()
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $count = (int) $counts->sum();
        $weighted = $counts->reduce(fn ($carry, $total, $rating) => $carry + ((int) $rating * (int) $total), 0);

        $breakdown = [];
        foreach ([5, 4, 3, 2, 1] as $star) {
            $n = (int) ($counts[$star] ?? 0);
            $breakdown[$star] = [
                'count' => $n,
                'percent' => $count > 0 ? (int) round($n / $count * 100) : 0,
            ];
        }

        return $this->reviewSummaryCache = [
            'average' => $count > 0 ? round($weighted / $count, 1) : 0.0,
            'count' => $count,
            'breakdown' => $breakdown,
        ];
    }

    public function imageUrl(): string
    {
        return $this->imageUrlLarge();
    }

    /**
     * All image URLs for the product detail slider: the main image plus any
     * gallery images, de-duplicated. Falls back to just the main image (or
     * placeholder) if no gallery is set.
     *
     * @return array<int, string>
     */
    public function galleryUrls(): array
    {
        $urls = [$this->imageUrlLarge()];

        foreach ((array) ($this->gallery ?? []) as $path) {
            if (! $path) {
                continue;
            }

            try {
                $urls[] = Storage::disk('s3')->url($path);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Product gallery image URL generation failed: ' . $e->getMessage(), ['exception' => $e]);
            }
        }

        return array_values(array_unique($urls));
    }

    public function imageUrlSmall(): string
    {
        return $this->resolveVariantUrl($this->image_small ?? $this->image);
    }

    public function imageUrlMedium(): string
    {
        return $this->resolveVariantUrl($this->image_medium ?? $this->image);
    }

    public function imageUrlLarge(): string
    {
        return $this->resolveVariantUrl($this->image_large ?? $this->image);
    }

    private function resolveVariantUrl(?string $path): string
    {
        if (! $path) {
            return asset('images/placeholder.png');
        }

        try {
            return Storage::disk('s3')->url($path);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Product image URL generation failed: ' . $e->getMessage(), ['exception' => $e]);
            return asset('images/placeholder.png');
        }
    }

    public function finalPrice(): float
    {
        return (float) ($this->discount_price ?? $this->price);
    }

    public function discountPercent(): ?int
    {
        if (!$this->discount_price) {
            return null;
        }
        return (int) round((($this->price - $this->discount_price) / $this->price) * 100);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}