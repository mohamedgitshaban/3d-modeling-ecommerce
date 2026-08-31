<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockItem extends Model
{
    protected $fillable = [
        'product_variant_id', 'sales_channel_id', 'quantity_on_hand',
        'quantity_reserved', 'low_stock_threshold', 'backorder_allowed',
    ];

    protected $casts = ['backorder_allowed' => 'boolean'];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function salesChannel(): BelongsTo
    {
        return $this->belongsTo(SalesChannel::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function quantityAvailable(): int
    {
        return max($this->quantity_on_hand - $this->quantity_reserved, 0);
    }
}
