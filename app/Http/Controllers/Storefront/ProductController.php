<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Pricing\PriceResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Request $request, Product $product, PriceResolver $priceResolver): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category.attributeGroups.fields', 'attributeValues.attribute', 'variants.optionValues.attribute', 'variants.stockItems']);

        $variant = $request->filled('variant')
            ? $product->variants->firstWhere('id', (int) $request->query('variant'))
            : null;

        $variant ??= $product->defaultVariant();

        $pricing = $variant ? $priceResolver->priceFor($variant) : null;

        // JSON map the Alpine.js variant-picker uses client-side to resolve a
        // combination of option choices to a concrete variant id, price, sku, stock.
        $variantMap = $product->variants->map(fn ($v) => [
            'id' => $v->id,
            'sku' => $v->sku,
            'price' => (float) $v->price,
            'options' => $v->optionLabels(),
            'stock_status' => $v->stockStatus(),
        ]);

        $specSections = $product->specSections();

        return view('storefront.products.show', compact('product', 'variant', 'pricing', 'variantMap', 'specSections'));
    }
}
