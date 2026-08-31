<?php

namespace App\Services\Pricing;

use App\Models\Cart;
use App\Models\ShippingRate;

class CartTotalsCalculator
{
    public function __construct(protected CouponValidator $couponValidator) {}

    /**
     * @return array{subtotal: float, discount_total: float, shipping_total: float, tax_total: float, grand_total: float}
     */
    public function totals(Cart $cart, ?ShippingRate $shippingRate = null, float $taxRatePercent = 0.0): array
    {
        $subtotal = $cart->subtotal();
        $discountTotal = 0.0;

        if ($cart->coupon && $cart->coupon->isCurrentlyActive()) {
            $discountTotal = $cart->coupon->type === 'free_shipping'
                ? 0.0
                : $this->couponValidator->discountAmount($cart->coupon, $subtotal);
        }

        $shippingTotal = 0.0;

        if ($shippingRate) {
            $shippingTotal = $shippingRate->costFor($subtotal);

            if ($cart->coupon && $cart->coupon->type === 'free_shipping' && $cart->coupon->isCurrentlyActive()) {
                $shippingTotal = 0.0;
            }
        }

        $taxableAmount = max($subtotal - $discountTotal, 0);
        $taxTotal = round($taxableAmount * ($taxRatePercent / 100), 2);

        $grandTotal = round($subtotal - $discountTotal + $shippingTotal + $taxTotal, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount_total' => round($discountTotal, 2),
            'shipping_total' => round($shippingTotal, 2),
            'tax_total' => $taxTotal,
            'grand_total' => $grandTotal,
        ];
    }
}
