<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryAttribute extends Model
{
    protected $fillable = [
        'category_attribute_group_id', 'key', 'label', 'input_type',
        'options', 'is_variant_option', 'is_filterable', 'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_variant_option' => 'boolean',
        'is_filterable' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(CategoryAttributeGroup::class, 'category_attribute_group_id');
    }
}
