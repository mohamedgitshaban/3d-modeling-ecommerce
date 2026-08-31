<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Lets any customer — guest or logged in — track an order's shipment status
 * after payment, using the order number plus the email it was placed under.
 * No account/login required.
 */
class OrderTrackingController extends Controller
{
    public function create(): View
    {
        return view('storefront.orders.track-lookup');
    }

    public function lookup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order_number' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $order = Order::where('order_number', $data['order_number'])
            ->where('customer_email', $data['email'])
            ->first();

        if (! $order) {
            return back()->withErrors(['order_number' => 'We could not find an order matching those details.'])->withInput();
        }

        return redirect()->route('orders.track.show', [
            'order' => $order->order_number,
            'token' => $order->tracking_token,
        ]);
    }

    public function show(Request $request, string $order): View
    {
        $order = Order::where('order_number', $order)->firstOrFail();

        abort_unless($this->canView($request, $order), 403, 'Invalid or expired tracking link.');

        $order->load('items', 'statusHistories', 'shippingAddress');

        return view('storefront.orders.track-show', [
            'order' => $order,
            'timeline' => $order->trackingTimeline(),
        ]);
    }

    /**
     * Lightweight JSON endpoint the tracking page polls for live status,
     * without requiring a websocket connection for guest visitors.
     */
    public function status(Request $request, string $order): JsonResponse
    {
        $order = Order::where('order_number', $order)->firstOrFail();

        abort_unless($this->canView($request, $order), 403);

        return response()->json([
            'status' => $order->status,
            'timeline' => $order->trackingTimeline(),
            'carrier' => $order->carrier,
            'tracking_number' => $order->tracking_number,
            'carrier_tracking_url' => $order->carrier_tracking_url,
            'estimated_delivery_at' => $order->estimated_delivery_at?->toDateString(),
        ]);
    }

    protected function canView(Request $request, Order $order): bool
    {
        return $order->isViewableBy($request->query('token'), $request->user()?->id);
    }
}
