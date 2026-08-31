<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    protected $fillable = [
        'shipping_zone_id', 'name', 'calculation_type', 'base_rate', 'rate_per_kg',
        'free_over_amount', 'estimated_days_min', 'estimated_days_max', 'is_active',
    ];

    protected $casts = [
        'base_rate' => 'decimal:2',
        'rate_per_kg' => 'decimal:2',
        'free_over_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function costFor(float $cartSubtotal, float $totalWeightKg = 0): float
    {
        return match ($this->calculation_type) {
            'flat' => (float) $this->base_rate,
            'weight_based' => (float) $this->base_rate + ($totalWeightKg * (float) $this->rate_per_kg),
            'free_over_threshold' => $this->free_over_amount !== null && $cartSubtotal >= $this->free_over_amount
                ? 0.0
                : (float) $this->base_rate,
            default => (float) $this->base_rate,
        };
    }

    public function estimatedDeliveryLabel(): string
    {
        if (! $this->estimated_days_min) {
            return '';
        }

        return $this->estimated_days_max && $this->estimated_days_max !== $this->estimated_days_min
            ? "{$this->estimated_days_min}-{$this->estimated_days_max} business days"
            : "{$this->estimated_days_min} business days";
    }
}
