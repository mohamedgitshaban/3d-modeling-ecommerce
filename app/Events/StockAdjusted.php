<?php

namespace App\Events;

use App\Models\StockItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast whenever a variant's stock changes so the storefront can update
 * its "In Stock / Low Stock / Out of Stock" badge live, on the public
 * `stock.{sku}` channel.
 *
 * Queued (not ShouldBroadcastNow) so a transiently unreachable Reverb server
 * never fails the stock write itself (including the reservation made during
 * checkout) — the live badge update just lands a beat late via the queue.
 */
class StockAdjusted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Wait until the enclosing DB transaction (InventoryService wraps every
     * write in one) actually commits before this is picked off the queue —
     * otherwise a worker could broadcast stale pre-commit stock numbers.
     */
    public $afterCommit = true;

    public function __construct(public StockItem $stockItem) {}

    public function broadcastOn(): array
    {
        return [new Channel('stock.'.$this->stockItem->variant->sku)];
    }

    public function broadcastAs(): string
    {
        return 'StockAdjusted';
    }

    public function broadcastWith(): array
    {
        $variant = $this->stockItem->variant;

        return [
            'sku' => $variant->sku,
            'quantity_available' => $variant->fresh('stockItems')->totalAvailable(),
            'status' => $variant->fresh('stockItems')->stockStatus(),
        ];
    }
}
