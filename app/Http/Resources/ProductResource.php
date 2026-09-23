<?php

namespace App\Http\Resources;

use App\Services\CartPricing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    /** Include description, SKU and gallery (product detail page). */
    private bool $detailed = false;

    public function detailed(): static
    {
        $this->detailed = true;

        return $this;
    }

    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => (float) $this->price,
            'discount_price' => $this->discount_price !== null ? (float) $this->discount_price : null,
            'final_price' => $this->finalPrice(),
            'discount_percent' => $this->discountPercent(),
            'stock' => (int) $this->stock,
            'max_qty' => CartPricing::maxQty($this->resource),
            'is_featured' => (bool) $this->is_featured,
            'image' => $this->imageUrlLarge(),
            'image_small' => $this->imageUrlSmall(),
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
        ];

        if ($this->detailed) {
            $data += [
                'description' => $this->description,
                'sku' => $this->sku,
                'gallery' => $this->galleryUrls(),
            ];
        }

        return $data;
    }
}
