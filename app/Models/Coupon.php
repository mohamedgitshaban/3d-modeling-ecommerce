<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_order_amount', 'max_discount_amount',
        'usage_limit', 'usage_limit_per_customer', 'times_used',
        'starts_at', 'expires_at', 'scope', 'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopes(): HasMany
    {
        return $this->hasMany(CouponScope::class);
    }

    public function isCurrentlyActive(): bool
    {
        $now = now();

        return $this->is_active
            && (! $this->starts_at || $this->starts_at->lte($now))
            && (! $this->expires_at || $this->expires_at->gte($now))
            && (! $this->usage_limit || $this->times_used < $this->usage_limit);
    }
}
