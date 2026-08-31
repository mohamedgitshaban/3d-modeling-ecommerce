<?php

namespace App\Services\Pricing;

use App\Models\ProductCollection;
use App\Models\ProductVariant;

class CollectionPriceCalculator
{
    /**
     * @param  array<int,int>  $selectedVariantIdsBySlotId
     */
    public function priceFor(ProductCollection $collection, array $selectedVariantIdsBySlotId): float
    {
        if ($collection->pricing_mode === 'fixed') {
            return (float) $collection->fixed_price;
        }

        $sum = 0.0;

        foreach ($selectedVariantIdsBySlotId as $variantId) {
            $variant = ProductVariant::find($variantId);
            $sum += $variant ? (float) $variant->price : 0.0;
        }

        if ($collection->discount_percent) {
            $sum -= $sum * ((float) $collection->discount_percent / 100);
        }

        return round($sum, 2);
    }
}
