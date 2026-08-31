<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        $featuredProducts = Product::with(['category', 'variants'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->take(8)
            ->get();

        $activeOffers = Offer::activeNow()->orderByDesc('priority')->take(4)->get();

        return view('storefront.home', compact('featuredCategories', 'featuredProducts', 'activeOffers'));
    }
}
