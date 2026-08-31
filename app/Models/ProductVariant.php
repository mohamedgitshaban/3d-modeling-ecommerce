<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProductVariant extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'product_id', 'sku', 'price', 'compare_at_price', 'weight',
        'dimensions', 'barcode', 'is_default', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('models_3d');
        $this->addMediaCollection('model_posters')->singleFile();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues(): HasMany
    {
        return $this->hasMany(VariantOptionValue::class);
    }

    public function stockItems(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }

    /**
     * Total sellable quantity across all sales channels (on hand minus reserved).
     */
    public function totalAvailable(): int
    {
        return (int) $this->stockItems->sum(fn (StockItem $item) => max($item->quantity_on_hand - $item->quantity_reserved, 0));
    }

    public function stockStatus(): string
    {
        $available = $this->totalAvailable();
        $lowThreshold = (int) ($this->stockItems->min('low_stock_threshold') ?? 5);

        if ($available <= 0) {
            return 'out_of_stock';
        }

        if ($available <= $lowThreshold) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * "Handle Finish: Matte Black" style label map for the buybox / cart / order snapshot.
     */
    public function optionLabels(): array
    {
        return $this->optionValues->mapWithKeys(fn (VariantOptionValue $ov) => [
            $ov->attribute->label => $ov->value,
        ])->all();
    }

    /**
     * Falls back to the parent product's 3D model / poster when the variant has none of its own,
     * so a finish that shares geometry with its siblings doesn't require a re-upload.
     */
    public function model3dUrl(string $extension = 'glb'): ?string
    {
        $media = $this->getMedia('models_3d')->first(fn ($m) => str_ends_with($m->file_name, ".{$extension}"))
            ?? $this->product->getMedia('models_3d')->first(fn ($m) => str_ends_with($m->file_name, ".{$extension}"));

        return $media?->getUrl();
    }

    public function model3dPoster(): ?string
    {
        return $this->getFirstMediaUrl('model_posters') ?: $this->product->model3dPoster();
    }

    public function galleryUrls(): array
    {
        $urls = $this->getMedia('gallery')->map->getUrl()->all();

        return $urls ?: $this->product->getMedia('gallery')->map->getUrl()->all();
    }
}
