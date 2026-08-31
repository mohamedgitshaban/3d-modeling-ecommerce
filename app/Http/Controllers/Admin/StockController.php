<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\SalesChannel;
use App\Models\StockItem;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $stockItems = StockItem::with('variant.product', 'salesChannel')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->query('q');
                $q->whereHas('variant', fn ($vq) => $vq->where('sku', 'like', "%{$search}%"));
            })
            ->when($request->boolean('low_stock'), fn ($q) => $q->whereColumn('quantity_on_hand', '<=', 'low_stock_threshold'))
            ->paginate(30)
            ->withQueryString();

        $channels = SalesChannel::orderBy('name')->get();

        return view('admin.stock.index', compact('stockItems', 'channels'));
    }

    public function adjust(Request $request, ProductVariant $variant, InventoryService $inventory): RedirectResponse
    {
        $data = $request->validate([
            'sales_channel_id' => ['required', 'exists:sales_channels,id'],
            'delta' => ['required', 'integer'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $channel = SalesChannel::findOrFail($data['sales_channel_id']);
        $inventory->adjust($variant, $channel, $data['delta'], 'restock', $data['note'] ?? null, $request->user()->id);

        return back()->with('status', 'Stock adjusted.');
    }

    public function updateThreshold(Request $request, StockItem $stockItem): RedirectResponse
    {
        $data = $request->validate([
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'backorder_allowed' => ['nullable', 'boolean'],
        ]);

        $stockItem->update($data);

        return back()->with('status', 'Stock settings updated.');
    }
}
