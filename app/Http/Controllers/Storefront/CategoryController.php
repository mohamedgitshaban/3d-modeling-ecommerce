<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryAttribute;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Request $request, Category $category): View
    {
        abort_unless($category->is_active, 404);

        $query = $category->products()->with(['variants', 'brand'])->where('is_active', true);

        // simple facet filter: ?finish=Matte+Black&size=24"
        foreach ($request->query() as $key => $value) {
            if (in_array($key, ['page'], true) || $value === null || $value === '') {
                continue;
            }

            $query->whereHas('attributeValues', function ($q) use ($key, $value) {
                $q->whereHas('attribute', fn ($aq) => $aq->where('key', $key))
                    ->where('value', $value);
            });
        }

        $products = $query->paginate(24)->withQueryString();

        $filterableAttributes = $category->variantOptionAttributes()
            ->merge(
                CategoryAttribute::whereHas('group', fn ($q) => $q->where('category_id', $category->id))
                    ->where('is_filterable', true)
                    ->get()
            )->unique('id');

        return view('storefront.categories.show', compact('category', 'products', 'filterableAttributes'));
    }
}
