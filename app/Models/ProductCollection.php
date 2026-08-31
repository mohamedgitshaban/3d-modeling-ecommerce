<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * A merchandised bundle that lets a shopper pick one product/variant per
 * category "slot" (e.g. Faucet + Vanity + Mirror) and buy it as a single item.
 *
 * Named ProductCollection (table: collections) to avoid clashing with
 * Illuminate\Support\Collection throughout the codebase.
 */
class ProductCollection extends Model implements HasMedia
{
    use HasFactory, HasSlug, InteractsWithMedia;

    protected $table = 'collections';

    protected $fillable = [
        'name', 'slug', 'description', 'pricing_mode',
        'fixed_price', 'discount_percent', 'is_active',
    ];

    protected $casts = [
        'fixed_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(CollectionSlot::class, 'collection_id')->orderBy('sort_order');
    }
}
