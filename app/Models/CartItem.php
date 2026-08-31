<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id', 'itemable_type', 'itemable_id', 'quantity',
        'collection_selection', 'unit_price',
    ];

    protected $casts = [
        'collection_selection' => 'array',
        'unit_price' => 'decimal:2',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * The purchasable this line represents: a ProductVariant, or a ProductCollection
     * (with the shopper's per-slot picks stored in collection_selection).
     */
    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    public function lineTotal(): float
    {
        return (float) $this->unit_price * $this->quantity;
    }

    public function displayName(): string
    {
        $item = $this->itemable;

        return $item instanceof ProductVariant ? $item->product->name : ($item->name ?? 'Item');
    }
}
