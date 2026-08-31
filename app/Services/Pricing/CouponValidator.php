<?php

namespace App\Services\Pricing;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\ProductCollection;
use App\Models\ProductVariant;
use Illuminate\Validation\ValidationException;

class CouponValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(Coupon $coupon, Cart $cart, ?int $customerId = null): void
    {
        if (! $coupon->isCurrentlyActive()) {
            throw ValidationException::withMessages(['coupon' => 'This coupon is no longer active.']);
        }

        $subtotal = $cart->subtotal();

        if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
            throw ValidationException::withMessages([
                'coupon' => "This coupon requires a minimum order of {$coupon->min_order_amount}.",
            ]);
        }

        if ($coupon->scope !== 'all' && ! $this->cartMatchesScope($coupon, $cart)) {
            throw ValidationException::withMessages(['coupon' => 'This coupon does not apply to the items in your cart.']);
        }
    }

    protected function cartMatchesScope(Coupon $coupon, Cart $cart): bool
    {
        $scopedIds = $coupon->scopes->pluck('scopable_id')->all();
        $scopedType = $coupon->scope;

        foreach ($cart->items as $item) {
            $itemable = $item->itemable;

            $matches = match ($scopedType) {
                'product' => $itemable instanceof ProductVariant && in_array($itemable->product_id, $scopedIds, true),
                'category' => $itemable instanceof ProductVariant && in_array($itemable->product->category_id, $scopedIds, true),
                'collection' => $itemable instanceof ProductCollection && in_array($itemable->id, $scopedIds, true),
                default => false,
            };

            if ($matches) {
                return true;
            }
        }

        return false;
    }

    public function discountAmount(Coupon $coupon, float $subtotal): float
    {
        $discount = match ($coupon->type) {
            'percentage' => $subtotal * ((float) $coupon->value / 100),
            'fixed_amount' => (float) $coupon->value,
            'free_shipping' => 0.0, // handled separately as shipping_total = 0
            default => 0.0,
        };

        if ($coupon->max_discount_amount) {
            $discount = min($discount, (float) $coupon->max_discount_amount);
        }

        return round(min($discount, $subtotal), 2);
    }
}
