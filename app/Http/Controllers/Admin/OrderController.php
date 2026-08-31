<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Orders\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->query('q');
                $q->where(fn ($sq) => $sq->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load('items', 'statusHistories.changedBy', 'payments', 'shippingAddress', 'user');

        return view('admin.orders.show', [
            'order' => $order,
            'timeline' => $order->trackingTimeline(),
        ]);
    }

    /**
     * Move an order to the next status in its lifecycle (processing, delivered,
     * cancelled, refunded) — anything except "shipped", which requires carrier info.
     */
    public function updateStatus(Request $request, Order $order, OrderStatusService $service): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:processing,delivered,cancelled,refunded'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $service->transitionTo($order, $data['status'], $data['note'] ?? null, $request->user()->id);

        return back()->with('status', 'Order status updated.');
    }

    /**
     * Mark an order shipped with carrier + tracking number — the core of the
     * order-tracking feature: this is what powers the customer-facing timeline.
     */
    public function markShipped(Request $request, Order $order, OrderStatusService $service): RedirectResponse
    {
        $data = $request->validate([
            'carrier' => ['required', 'string', 'max:100'],
            'tracking_number' => ['required', 'string', 'max:150'],
            'carrier_tracking_url' => ['nullable', 'url', 'max:500'],
        ]);

        $service->markShipped(
            $order,
            $data['carrier'],
            $data['tracking_number'],
            $data['carrier_tracking_url'] ?? null,
            $request->user()->id
        );

        return back()->with('status', 'Order marked as shipped — customer can now track it.');
    }
}
