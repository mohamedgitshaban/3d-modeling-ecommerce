<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreLocationController extends Controller
{
    public function index(): View
    {
        $stores = StoreLocation::orderBy('name')->paginate(20);

        return view('admin.store-locations.index', compact('stores'));
    }

    public function store(Request $request): RedirectResponse
    {
        StoreLocation::create($this->validated($request));

        return back()->with('status', 'Store location added.');
    }

    public function update(Request $request, StoreLocation $storeLocation): RedirectResponse
    {
        $storeLocation->update($this->validated($request));

        return back()->with('status', 'Store location updated.');
    }

    public function destroy(StoreLocation $storeLocation): RedirectResponse
    {
        $storeLocation->delete();

        return back()->with('status', 'Store location deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'country' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
