<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Catalog\ProductVariantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    /**
     * Generate the Cartesian product of the category's variant-driving
     * attributes (e.g. Handle Finish x Vanity Finish x Size) into draft variants.
     */
    public function generate(Request $request, Product $product, ProductVariantService $service): RedirectResponse
    {
        $data = $request->validate([
            'options' => ['required', 'array'], // category_attribute_id => comma separated values
        ]);

        $selected = [];

        foreach ($data['options'] as $attributeId => $csv) {
            $values = array_filter(array_map('trim', explode(',', (string) $csv)));

            if ($values) {
                $selected[$attributeId] = $values;
            }
        }

        $created = $service->generateFromAttributes($product, $selected);

        return back()->with('status', count($created).' variant combination(s) generated. Set price/SKU/stock and activate each.');
    }

    public function update(Request $request, ProductVariant $variant): RedirectResponse
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:150', 'unique:product_variants,sku,'.$variant->id],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! empty($data['is_default'])) {
            $variant->product->variants()->update(['is_default' => false]);
        }

        $variant->update($data);

        return back()->with('status', 'Variant updated.');
    }

    public function destroy(ProductVariant $variant): RedirectResponse
    {
        $variant->delete();

        return back()->with('status', 'Variant removed.');
    }
}
