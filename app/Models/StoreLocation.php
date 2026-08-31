<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreLocation extends Model
{
    protected $fillable = [
        'name', 'address_line_1', 'address_line_2', 'city', 'state', 'postal_code',
        'country', 'latitude', 'longitude', 'phone', 'website', 'hours', 'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'hours' => 'array',
        'is_active' => 'boolean',
    ];

    public function toSingleLine(): string
    {
        return collect([
            $this->address_line_1, $this->address_line_2, $this->city,
            $this->state, $this->postal_code, $this->country,
        ])->filter()->implode(', ');
    }
}
