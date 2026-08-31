<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingController extends Controller
{
    public function index(): View
    {
        $zones = ShippingZone::with('rates')->get();

        return view('admin.shipping.index', compact('zones'));
    }

    public function storeZone(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'countries' => ['required', 'string'], // comma separated ISO country names/codes
            'is_active' => ['nullable', 'boolean'],
        ]);

        ShippingZone::create([
            'name' => $data['name'],
            'countries' => array_map('trim', explode(',', $data['countries'])),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Shipping zone created.');
    }

    public function destroyZone(ShippingZone $zone): RedirectResponse
    {
        $zone->delete();

        return back()->with('status', 'Shipping zone deleted.');
    }

    public function storeRate(Request $request, ShippingZone $zone): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'calculation_type' => ['required', 'in:flat,weight_based,free_over_threshold'],
            'base_rate' => ['required', 'numeric', 'min:0'],
            'rate_per_kg' => ['nullable', 'numeric', 'min:0'],
            'free_over_amount' => ['nullable', 'numeric', 'min:0'],
            'estimated_days_min' => ['nullable', 'integer', 'min:0'],
            'estimated_days_max' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $zone->rates()->create($data);

        return back()->with('status', 'Shipping rate added.');
    }

    public function destroyRate(ShippingZone $zone, ShippingRate $rate): RedirectResponse
    {
        abort_unless($rate->shipping_zone_id === $zone->id, 404);
        $rate->delete();

        return back()->with('status', 'Shipping rate deleted.');
    }
}
