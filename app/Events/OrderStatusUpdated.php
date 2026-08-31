<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast on every order status change (paid, processing, shipped, delivered, ...)
 * so a logged-in customer's "My Orders" page updates its tracking timeline live.
 *
 * Queued (not ShouldBroadcastNow) so a transiently unreachable Reverb server
 * never fails the request that changed the order's status — worst case the
 * live push is delayed a beat and the customer sees it on their next poll
 * or page load instead of instantly.
 */
class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('order.'.$this->order->id)];
    }

    public function broadcastAs(): string
    {
        return 'OrderStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'timeline' => $this->order->trackingTimeline(),
            'tracking_number' => $this->order->tracking_number,
            'carrier' => $this->order->carrier,
            'carrier_tracking_url' => $this->order->carrier_tracking_url,
        ];
    }
}
