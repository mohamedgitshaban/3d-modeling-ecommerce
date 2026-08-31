<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\SalesChannel;
use App\Services\Inventory\InventoryService;
use App\Services\Orders\OrderStatusService;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleOrderPaid implements ShouldQueue
{
    public function __construct(
        protected InventoryService $inventory,
        protected OrderStatusService $orderStatus,
    ) {}

    public function handle(OrderPaid $event): void
    {
        $order = $event->order;
        $channel = SalesChannel::where('code', 'online')->first();

        if ($channel) {
            foreach ($order->items as $item) {
                if ($item->itemable_type === 'product_variant') {
                    $this->releaseVariantStock($item->itemable, $channel, $item->quantity, $order->id);
                } elseif ($item->itemable_type === 'collection') {
                    foreach ((array) $item->collection_selection as $variantId) {
                        if ($variant = ProductVariant::find($variantId)) {
                            $this->releaseVariantStock($variant, $channel, $item->quantity, $order->id);
                        }
                    }
                }
            }
        }

        $this->orderStatus->transitionTo($order, Order::STATUS_PAID, 'Payment confirmed via Paymob.');
    }

    protected function releaseVariantStock(?ProductVariant $variant, SalesChannel $channel, int $quantity, int $orderId): void
    {
        if ($variant) {
            $this->inventory->release($variant, $channel, $quantity, consumeStock: true, orderId: $orderId);
        }
    }
}
