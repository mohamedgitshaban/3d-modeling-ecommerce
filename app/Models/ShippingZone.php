<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = ['name', 'countries', 'is_active'];

    protected $casts = [
        'countries' => 'array',
        'is_active' => 'boolean',
    ];

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }

    public static function findForCountry(string $country): ?self
    {
        return static::where('is_active', true)
            ->get()
            ->first(fn (self $zone) => in_array($country, $zone->countries, true));
    }
}
