<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CollectionSlot;
use App\Models\ProductCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollectionController extends Controller
{
    public function index(): View
    {
        $collections = ProductCollection::withCount('slots')->latest()->paginate(20);

        return view('admin.collections.index', compact('collections'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.collections.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $collection = ProductCollection::create($this->validated($request));

        return redirect()->route('admin.collections.edit', $collection)->with('status', 'Collection created. Add its slots below.');
    }

    public function edit(ProductCollection $collection): View
    {
        $collection->load('slots.category', 'slots.defaultVariant.product');
        $categories = Category::orderBy('name')->get();

        return view('admin.collections.edit', compact('collection', 'categories'));
    }

    public function update(Request $request, ProductCollection $collection): RedirectResponse
    {
        $collection->update($this->validated($request));

        return back()->with('status', 'Collection updated.');
    }

    public function destroy(ProductCollection $collection): RedirectResponse
    {
        $collection->delete();

        return redirect()->route('admin.collections.index')->with('status', 'Collection deleted.');
    }

    public function storeSlot(Request $request, ProductCollection $collection): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'label' => ['required', 'string', 'max:255'],
            'is_required' => ['nullable', 'boolean'],
            'default_product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $collection->slots()->create($data);

        return back()->with('status', 'Slot added.');
    }

    public function destroySlot(ProductCollection $collection, CollectionSlot $slot): RedirectResponse
    {
        abort_unless($slot->collection_id === $collection->id, 404);
        $slot->delete();

        return back()->with('status', 'Slot removed.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'pricing_mode' => ['required', 'in:fixed,sum_of_selections'],
            'fixed_price' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
