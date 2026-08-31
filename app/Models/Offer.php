<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'name', 'type', 'value', 'target_type', 'target_id',
        'starts_at', 'ends_at', 'badge_label', 'priority', 'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isWithinWindow(): bool
    {
        $now = now();

        return $this->starts_at->lte($now) && $this->ends_at->gte($now);
    }

    public function scopeActiveNow($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }
}
