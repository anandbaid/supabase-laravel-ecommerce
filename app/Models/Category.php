<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'image', 'image_small', 'image_medium', 'image_large',
        'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name) . '-' . Str::random(4);
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function isSubcategory(): bool
    {
        return !is_null($this->parent_id);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * IDs of this category and, if it's a top-level category, all its
     * subcategories. Used to include subcategory products when browsing
     * by a parent category.
     */
    public function selfAndDescendantIds(): array
    {
        return array_merge([$this->id], $this->children()->pluck('id')->all());
    }

    /**
     * Default/full-size image URL. Kept for backward compatibility with
     * existing views; equivalent to imageUrlLarge().
     */
    public function imageUrl(): string
    {
        return $this->imageUrlLarge();
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
            \Illuminate\Support\Facades\Log::error('Category image URL generation failed: ' . $e->getMessage(), ['exception' => $e]);
            return asset('images/placeholder.png');
        }
    }
}
