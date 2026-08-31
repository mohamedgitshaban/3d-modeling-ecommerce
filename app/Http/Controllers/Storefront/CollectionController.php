<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\ProductCollection;
use App\Services\Pricing\CollectionPriceCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollectionController extends Controller
{
    public function show(Request $request, ProductCollection $collection, CollectionPriceCalculator $calculator): View
    {
        abort_unless($collection->is_active, 404);

        $collection->load('slots.category', 'slots.defaultVariant.product');

        $selection = [];

        foreach ($collection->slots as $slot) {
            $selection[$slot->id] = (int) $request->query("slot_{$slot->id}", $slot->default_product_variant_id);
        }

        $price = $calculator->priceFor($collection, $selection);

        return view('storefront.collections.show', compact('collection', 'selection', 'price'));
    }
}
