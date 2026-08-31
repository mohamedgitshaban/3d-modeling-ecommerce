<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryAttributeGroup extends Model
{
    protected $fillable = ['category_id', 'key', 'label', 'type', 'sort_order'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The individual fields inside this section (e.g. Finish, Flow Rate).
     *
     * Deliberately not named attributes() — that collides with Eloquent's
     * internal Model::$attributes property and silently returns an array
     * instead of the relation.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(CategoryAttribute::class)->orderBy('sort_order');
    }
}
