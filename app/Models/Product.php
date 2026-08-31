<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model implements HasMedia
{
    use HasFactory, HasSlug, InteractsWithMedia;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'base_sku', 'msrp',
        'collection_line', 'short_description', 'is_active', 'is_featured',
        'meta_title', 'meta_description',
    ];

    protected $casts = [
        'msrp' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('models_3d');
        $this->addMediaCollection('model_posters')->singleFile();
        $this->addMediaCollection('documents'); // warranty / spec sheets / install guides
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant(): ?ProductVariant
    {
        return $this->variants->firstWhere('is_default', true) ?? $this->variants->first();
    }

    public function breadcrumbTrail(): array
    {
        return [...$this->category->breadcrumbTrail(), $this];
    }

    /**
     * Group this product's attribute values by the category's attribute-group schema,
     * ready for rendering (Overview, More Features, Certifications, Specifications, Info & Guides).
     */
    public function specSections(): array
    {
        $values = $this->attributeValues->keyBy('category_attribute_id');

        return $this->category->attributeGroups->map(function (CategoryAttributeGroup $group) use ($values) {
            $items = $group->fields->map(function (CategoryAttribute $attribute) use ($values) {
                $value = $values->get($attribute->id);

                return [
                    'attribute' => $attribute,
                    'value' => $value?->value,
                ];
            })->filter(fn ($item) => filled($item['value']))->values();

            return [
                'group' => $group,
                'items' => $items,
            ];
        })->filter(fn ($section) => $section['items']->isNotEmpty())->values()->all();
    }

    public function warrantyDocuments(): Collection
    {
        return $this->getMedia('documents');
    }

    public function model3dPoster(): ?string
    {
        return $this->getFirstMediaUrl('model_posters') ?: null;
    }
}
