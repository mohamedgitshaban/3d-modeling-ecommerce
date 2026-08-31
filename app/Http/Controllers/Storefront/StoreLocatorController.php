<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\StoreLocation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreLocatorController extends Controller
{
    public function index(Request $request): View
    {
        $query = StoreLocation::where('is_active', true);

        if ($request->filled('q')) {
            $search = $request->query('q');
            $query->where(function ($q) use ($search) {
                $q->where('city', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%")
                    ->orWhere('postal_code', 'like', "%{$search}%");
            });
        }

        $stores = $query->orderBy('name')->get();

        return view('storefront.store-locator.index', compact('stores'));
    }
}
