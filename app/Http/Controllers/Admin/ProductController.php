<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('category', 'variants')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->query('q').'%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request): RedirectResponse
    {
        $product = Product::create($this->validated($request));

        return redirect()->route('admin.products.edit', $product)
            ->with('status', 'Product created. Fill in its specs, gallery, 3D model, and variants below.');
    }

    public function edit(Product $product): View
    {
        $product->load('category.attributeGroups.fields', 'attributeValues', 'variants.optionValues.attribute', 'variants.stockItems.salesChannel');
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validated($request, $product));

        return back()->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    /**
     * Save the dynamic spec-section values (Overview bullets, More Features,
     * Certifications, Specifications, Info & Guides) for this product.
     */
    public function updateAttributes(Request $request, Product $product): RedirectResponse
    {
        $values = $request->input('values', []); // category_attribute_id => value

        foreach ($values as $attributeId => $value) {
            if ($value === null || $value === '') {
                $product->attributeValues()->where('category_attribute_id', $attributeId)->delete();

                continue;
            }

            $product->attributeValues()->updateOrCreate(
                ['category_attribute_id' => $attributeId],
                ['value' => is_array($value) ? json_encode(array_values(array_filter($value))) : $value]
            );
        }

        return back()->with('status', 'Specifications saved.');
    }

    protected function validated(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:255'],
            'base_sku' => ['required', 'string', 'max:100'],
            'msrp' => ['nullable', 'numeric', 'min:0'],
            'collection_line' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
