<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockItem;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'products' => Product::count(),
            'orders_today' => Order::whereDate('created_at', today())->count(),
            'pending_orders' => Order::whereIn('status', [Order::STATUS_PAID, Order::STATUS_PROCESSING])->count(),
            'low_stock' => StockItem::whereColumn('quantity_on_hand', '<=', 'low_stock_threshold')->count(),
        ];

        $recentOrders = Order::with('items')->latest()->take(10)->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }
}
