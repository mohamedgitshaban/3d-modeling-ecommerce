<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function index(): View
    {
        $offers = Offer::latest()->paginate(20);

        return view('admin.offers.index', compact('offers'));
    }

    public function create(): View
    {
        $products = Product::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $collections = ProductCollection::orderBy('name')->get();

        return view('admin.offers.create', compact('products', 'categories', 'collections'));
    }

    public function store(Request $request): RedirectResponse
    {
        Offer::create($this->validated($request));

        return redirect()->route('admin.offers.index')->with('status', 'Offer created.');
    }

    public function update(Request $request, Offer $offer): RedirectResponse
    {
        $offer->update($this->validated($request));

        return back()->with('status', 'Offer updated.');
    }

    public function destroy(Offer $offer): RedirectResponse
    {
        $offer->delete();

        return redirect()->route('admin.offers.index')->with('status', 'Offer deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:percentage_off,fixed_off,bundle_discount'],
            'value' => ['required', 'numeric', 'min:0'],
            'target_type' => ['required', 'in:product,category,collection'],
            'target_id' => ['required', 'integer'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'badge_label' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
