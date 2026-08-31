<?php

namespace App\Services\Orders;

use App\Events\OrderStatusUpdated;
use App\Models\Order;

/**
 * Central place to transition an order's status — always writes an
 * order_status_histories row and broadcasts OrderStatusUpdated so the
 * order-tracking UI (and the customer's live "My Orders" page) stay in sync.
 */
class OrderStatusService
{
    public function transitionTo(Order $order, string $status, ?string $note = null, ?int $changedBy = null): Order
    {
        $order->status = $status;

        if ($status === Order::STATUS_SHIPPED && ! $order->shipped_at) {
            $order->shipped_at = now();
        }

        if ($status === Order::STATUS_DELIVERED && ! $order->delivered_at) {
            $order->delivered_at = now();
        }

        $order->save();

        $order->statusHistories()->create([
            'status' => $status,
            'note' => $note,
            'changed_by' => $changedBy,
        ]);

        OrderStatusUpdated::dispatch($order->fresh('statusHistories'));

        return $order;
    }

    public function markShipped(Order $order, string $carrier, string $trackingNumber, ?string $trackingUrl = null, ?int $changedBy = null): Order
    {
        $order->carrier = $carrier;
        $order->tracking_number = $trackingNumber;
        $order->carrier_tracking_url = $trackingUrl;
        $order->save();

        return $this->transitionTo($order, Order::STATUS_SHIPPED, "Shipped via {$carrier} ({$trackingNumber})", $changedBy);
    }
}
