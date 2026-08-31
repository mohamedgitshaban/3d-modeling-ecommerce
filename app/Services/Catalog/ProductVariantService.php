<?php

namespace App\Services\Catalog;

use App\Models\CategoryAttribute;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Generates the Cartesian product of a product category's variant-driving
 * attributes (e.g. Handle Finish x Vanity Finish x Size) into product_variants
 * rows. Prices/SKUs are left blank for the merchandiser to fill in — never
 * auto-priced.
 */
class ProductVariantService
{
    /**
     * @param  array<string, array<string>>  $selectedOptionValues  keyed by category_attribute_id => list of chosen values
     * @return ProductVariant[]
     */
    public function generateFromAttributes(Product $product, array $selectedOptionValues): array
    {
        $attributeIds = array_keys($selectedOptionValues);
        $attributes = CategoryAttribute::whereIn('id', $attributeIds)->get()->keyBy('id');

        $combinations = $this->cartesianProduct($selectedOptionValues);

        return DB::transaction(function () use ($product, $combinations) {
            $created = [];

            foreach ($combinations as $combination) {
                $skuSuffix = collect($combination)->map(fn ($value) => Str::upper(Str::slug($value, '')))->implode('-');
                $sku = "{$product->base_sku}-{$skuSuffix}";

                $variant = ProductVariant::firstOrCreate(
                    ['sku' => $sku],
                    [
                        'product_id' => $product->id,
                        'price' => $product->msrp ?? 0,
                        'is_active' => false, // merchandiser must confirm price/stock before activating
                    ]
                );

                foreach ($combination as $attributeId => $value) {
                    $variant->optionValues()->updateOrCreate(
                        ['category_attribute_id' => $attributeId],
                        ['value' => $value]
                    );
                }

                $created[] = $variant;
            }

            return $created;
        });
    }

    /**
     * @param  array<string, array<string>>  $options
     * @return array<int, array<string, string>>
     */
    protected function cartesianProduct(array $options): array
    {
        $result = [[]];

        foreach ($options as $attributeId => $values) {
            $append = [];

            foreach ($result as $combination) {
                foreach ($values as $value) {
                    $append[] = $combination + [$attributeId => $value];
                }
            }

            $result = $append;
        }

        return $result;
    }
}
