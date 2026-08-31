<?php

namespace App\Services\Inventory;

use App\Events\StockAdjusted;
use App\Models\ProductVariant;
use App\Models\SalesChannel;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

/**
 * All stock writes go through here so every change is journaled to
 * stock_movements and broadcast on the variant's real-time `stock.{sku}` channel.
 */
class InventoryService
{
    /**
     * Restock, correct, or otherwise directly set a delta on quantity_on_hand.
     */
    public function adjust(ProductVariant $variant, SalesChannel $channel, int $delta, string $type = 'adjustment', ?string $note = null, ?int $userId = null): StockItem
    {
        return DB::transaction(function () use ($variant, $channel, $delta, $type, $note, $userId) {
            $stockItem = StockItem::query()
                ->where('product_variant_id', $variant->id)
                ->where('sales_channel_id', $channel->id)
                ->lockForUpdate()
                ->first();

            if (! $stockItem) {
                $stockItem = StockItem::create([
                    'product_variant_id' => $variant->id,
                    'sales_channel_id' => $channel->id,
                    'quantity_on_hand' => 0,
                ]);
            }

            $stockItem->quantity_on_hand = max(0, $stockItem->quantity_on_hand + $delta);
            $stockItem->save();

            StockMovement::create([
                'stock_item_id' => $stockItem->id,
                'type' => $type,
                'quantity' => $delta,
                'quantity_after' => $stockItem->quantity_on_hand,
                'user_id' => $userId,
                'note' => $note,
            ]);

            StockAdjusted::dispatch($stockItem->fresh());

            return $stockItem;
        });
    }

    /**
     * Hold stock for an unpaid order (reserved, not yet decremented) so two
     * customers can't both check out with the last unit while payment is pending.
     */
    public function reserve(ProductVariant $variant, SalesChannel $channel, int $quantity, ?int $orderId = null): bool
    {
        return DB::transaction(function () use ($variant, $channel, $quantity, $orderId) {
            $stockItem = StockItem::query()
                ->where('product_variant_id', $variant->id)
                ->where('sales_channel_id', $channel->id)
                ->lockForUpdate()
                ->first();

            if (! $stockItem) {
                return false;
            }

            $available = $stockItem->quantity_on_hand - $stockItem->quantity_reserved;

            if ($available < $quantity && ! $stockItem->backorder_allowed) {
                return false;
            }

            $stockItem->quantity_reserved += $quantity;
            $stockItem->save();

            StockMovement::create([
                'stock_item_id' => $stockItem->id,
                'type' => 'reservation',
                'quantity' => $quantity,
                'quantity_after' => $stockItem->quantity_on_hand,
                'reference_type' => 'order',
                'reference_id' => $orderId,
            ]);

            StockAdjusted::dispatch($stockItem->fresh());

            return true;
        });
    }

    /**
     * Release a reservation — either because the order was cancelled/expired
     * (reserved only) or fulfilled (reserved AND decremented from on-hand).
     */
    public function release(ProductVariant $variant, SalesChannel $channel, int $quantity, bool $consumeStock = false, ?int $orderId = null): void
    {
        DB::transaction(function () use ($variant, $channel, $quantity, $consumeStock, $orderId) {
            $stockItem = StockItem::query()
                ->where('product_variant_id', $variant->id)
                ->where('sales_channel_id', $channel->id)
                ->lockForUpdate()
                ->first();

            if (! $stockItem) {
                return;
            }

            $stockItem->quantity_reserved = max(0, $stockItem->quantity_reserved - $quantity);

            if ($consumeStock) {
                $stockItem->quantity_on_hand = max(0, $stockItem->quantity_on_hand - $quantity);
            }

            $stockItem->save();

            StockMovement::create([
                'stock_item_id' => $stockItem->id,
                'type' => $consumeStock ? 'sale' : 'release',
                'quantity' => -$quantity,
                'quantity_after' => $stockItem->quantity_on_hand,
                'reference_type' => 'order',
                'reference_id' => $orderId,
            ]);

            StockAdjusted::dispatch($stockItem->fresh());
        });
    }
}
