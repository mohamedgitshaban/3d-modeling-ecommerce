<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'image', 'banner',
        'meta_title', 'meta_description', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function attributeGroups(): HasMany
    {
        return $this->hasMany(CategoryAttributeGroup::class)->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * All variant-driving attributes for this category (e.g. Handle Finish, Vanity Finish, Size).
     */
    public function variantOptionAttributes()
    {
        return CategoryAttribute::whereHas('group', fn ($q) => $q->where('category_id', $this->id))
            ->where('is_variant_option', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Breadcrumb trail from root ancestor down to this category.
     */
    public function breadcrumbTrail(): array
    {
        $trail = [];
        $node = $this;

        while ($node) {
            array_unshift($trail, $node);
            $node = $node->parent;
        }

        return $trail;
    }
}
