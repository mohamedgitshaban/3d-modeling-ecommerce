<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\SalesChannel;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Services\Cart\CartResolver;
use App\Services\Inventory\InventoryService;
use App\Services\Payments\PaymentGatewayContract;
use App\Services\Pricing\CartTotalsCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(protected CartResolver $cartResolver) {}

    public function show(Request $request): View
    {
        $cart = $this->cartResolver->current($request)->load('items.itemable', 'coupon');
        abort_if($cart->items->isEmpty(), 404, 'Your cart is empty.');

        $shippingZones = ShippingZone::with('rates')->where('is_active', true)->get();
        $addresses = $request->user()?->addresses ?? collect();

        return view('storefront.checkout.show', compact('cart', 'shippingZones', 'addresses'));
    }

    public function store(
        Request $request,
        CartTotalsCalculator $totalsCalculator,
        InventoryService $inventory,
        PaymentGatewayContract $paymentGateway
    ): RedirectResponse {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'shipping_rate_id' => ['required', 'exists:shipping_rates,id'],
            'address_line_1' => ['required', 'string'],
            'address_line_2' => ['nullable', 'string'],
            'city' => ['required', 'string'],
            'state' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string'],
            'country' => ['required', 'string'],
            'payment_method' => ['required', 'in:card,wallet,kiosk'],
        ]);

        $cart = $this->cartResolver->current($request)->load('items.itemable', 'coupon');
        abort_if($cart->items->isEmpty(), 404, 'Your cart is empty.');

        $shippingRate = ShippingRate::findOrFail($data['shipping_rate_id']);
        $totals = $totalsCalculator->totals($cart, $shippingRate);

        $order = DB::transaction(function () use ($request, $data, $cart, $shippingRate, $totals, $inventory) {
            $address = Address::create([
                'user_id' => $request->user()?->id,
                'full_name' => $data['customer_name'],
                'phone' => $data['customer_phone'] ?? '',
                'address_line_1' => $data['address_line_1'],
                'address_line_2' => $data['address_line_2'] ?? null,
                'city' => $data['city'],
                'state' => $data['state'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country' => $data['country'],
            ]);

            $order = Order::create([
                'user_id' => $request->user()?->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'status' => Order::STATUS_PENDING,
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'shipping_total' => $totals['shipping_total'],
                'tax_total' => $totals['tax_total'],
                'grand_total' => $totals['grand_total'],
                'coupon_id' => $cart->coupon_id,
                'shipping_address_id' => $address->id,
                'billing_address_id' => $address->id,
                'shipping_rate_id' => $shippingRate->id,
                'estimated_delivery_at' => $shippingRate->estimated_days_max
                    ? now()->addDays($shippingRate->estimated_days_max)
                    : null,
            ]);

            $channel = SalesChannel::firstOrCreate(['code' => 'online'], ['name' => 'Online Store']);

            foreach ($cart->items as $cartItem) {
                $itemable = $cartItem->itemable;

                $order->items()->create([
                    'itemable_type' => $cartItem->itemable_type,
                    'itemable_id' => $cartItem->itemable_id,
                    'name' => $cartItem->displayName(),
                    'sku' => $itemable instanceof ProductVariant ? $itemable->sku : null,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'line_total' => $cartItem->lineTotal(),
                    'collection_selection' => $cartItem->collection_selection,
                    'attributes_snapshot' => $itemable instanceof ProductVariant ? $itemable->optionLabels() : null,
                ]);

                if ($cartItem->itemable_type === 'product_variant') {
                    $inventory->reserve($itemable, $channel, $cartItem->quantity, $order->id);
                } elseif ($cartItem->itemable_type === 'collection') {
                    foreach ((array) $cartItem->collection_selection as $variantId) {
                        if ($variant = ProductVariant::find($variantId)) {
                            $inventory->reserve($variant, $channel, $cartItem->quantity, $order->id);
                        }
                    }
                }
            }

            $cart->items()->delete();

            return $order;
        });

        $initiation = $paymentGateway->initiate($order, $data['payment_method']);

        Payment::create([
            'order_id' => $order->id,
            'gateway' => 'paymob',
            'paymob_order_id' => $initiation->gatewayOrderId,
            'method' => $data['payment_method'],
            'status' => 'pending',
            'amount' => $order->grand_total,
            'raw_response' => $initiation->raw,
        ]);

        $order->update(['status' => Order::STATUS_AWAITING_PAYMENT]);

        return redirect()->away($initiation->redirectUrl);
    }

    public function thankYou(Request $request, Order $order): View
    {
        abort_unless(
            $order->isViewableBy($request->query('token'), $request->user()?->id),
            403
        );

        return view('storefront.checkout.thank-you', compact('order'));
    }
}
