<?php

use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;

// Public channel — no auth entry needed: anyone can watch a SKU's live stock badge.
// (Registered here only as documentation; `stock.{sku}` requires no authorizer.)

Broadcast::channel('order.{orderId}', function ($user, int $orderId) {
    $order = Order::find($orderId);

    if (! $order) {
        return false;
    }

    return $user->id === $order->user_id || $user->can('manage orders');
});
