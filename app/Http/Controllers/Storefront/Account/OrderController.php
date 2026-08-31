<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()->latest()->paginate(15);

        return view('storefront.account.orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load('items', 'statusHistories', 'shippingAddress');

        return view('storefront.account.orders.show', [
            'order' => $order,
            'timeline' => $order->trackingTimeline(),
        ]);
    }
}
