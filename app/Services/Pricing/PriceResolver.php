<?php

namespace App\Services\Pricing;

use App\Models\Offer;
use App\Models\ProductVariant;

/**
 * Resolves the live selling price for a variant, applying the highest-priority
 * active offer scoped to the variant's product, category, or a collection it's in.
 */
class PriceResolver
{
    public function priceFor(ProductVariant $variant): array
    {
        $base = (float) $variant->price;
        $offer = $this->bestOfferFor($variant);

        if (! $offer) {
            return [
                'price' => $base,
                'compare_at_price' => $variant->compare_at_price ? (float) $variant->compare_at_price : null,
                'offer' => null,
            ];
        }

        $discounted = match ($offer->type) {
            'percentage_off' => $base * (1 - ((float) $offer->value / 100)),
            'fixed_off' => max($base - (float) $offer->value, 0),
            default => $base,
        };

        return [
            'price' => round($discounted, 2),
            'compare_at_price' => $base,
            'offer' => $offer,
        ];
    }

    protected function bestOfferFor(ProductVariant $variant): ?Offer
    {
        return Offer::query()
            ->activeNow()
            ->where(function ($query) use ($variant) {
                $query->where(fn ($q) => $q->where('target_type', 'product')->where('target_id', $variant->product_id))
                    ->orWhere(fn ($q) => $q->where('target_type', 'category')->where('target_id', $variant->product->category_id));
            })
            ->orderByDesc('priority')
            ->first();
    }
}
