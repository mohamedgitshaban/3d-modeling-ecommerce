<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class CollectionSlot extends Model
{
    protected $fillable = [
        'collection_id', 'category_id', 'label', 'is_required',
        'allowed_product_ids', 'default_product_variant_id', 'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'allowed_product_ids' => 'array',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(ProductCollection::class, 'collection_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function defaultVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'default_product_variant_id');
    }

    /**
     * Products the shopper may choose from for this slot.
     */
    public function eligibleProducts(): Collection
    {
        $query = Product::query()->where('category_id', $this->category_id)->where('is_active', true);

        if (! empty($this->allowed_product_ids)) {
            $query->whereIn('id', $this->allowed_product_ids);
        }

        return $query->with('variants')->get();
    }
}
