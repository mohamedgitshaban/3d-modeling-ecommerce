<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\ProductCollection;
use App\Models\ProductVariant;
use App\Services\Cart\CartResolver;
use App\Services\Pricing\CartTotalsCalculator;
use App\Services\Pricing\CollectionPriceCalculator;
use App\Services\Pricing\CouponValidator;
use App\Services\Pricing\PriceResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(protected CartResolver $cartResolver) {}

    public function index(Request $request, CartTotalsCalculator $totalsCalculator): View
    {
        $cart = $this->cartResolver->current($request)->load('items.itemable', 'coupon');
        $totals = $totalsCalculator->totals($cart);

        return view('storefront.cart.index', compact('cart', 'totals'));
    }

    public function storeVariant(Request $request, PriceResolver $priceResolver): RedirectResponse
    {
        $data = $request->validate([
            'variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $variant = ProductVariant::findOrFail($data['variant_id']);
        $cart = $this->cartResolver->current($request);
        $unitPrice = $priceResolver->priceFor($variant)['price'];

        $item = $cart->items()->where('itemable_type', 'product_variant')->where('itemable_id', $variant->id)->first();

        if ($item) {
            $item->increment('quantity', $data['quantity'] ?? 1);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'itemable_type' => 'product_variant',
                'itemable_id' => $variant->id,
                'quantity' => $data['quantity'] ?? 1,
                'unit_price' => $unitPrice,
            ]);
        }

        return back()->with('status', 'Added to cart.');
    }

    public function storeCollection(Request $request, CollectionPriceCalculator $calculator): RedirectResponse
    {
        $data = $request->validate([
            'collection_id' => ['required', 'exists:collections,id'],
            'selection' => ['required', 'array'], // slot_id => variant_id
        ]);

        $collection = ProductCollection::findOrFail($data['collection_id']);
        $cart = $this->cartResolver->current($request);
        $price = $calculator->priceFor($collection, $data['selection']);

        CartItem::create([
            'cart_id' => $cart->id,
            'itemable_type' => 'collection',
            'itemable_id' => $collection->id,
            'quantity' => 1,
            'collection_selection' => $data['selection'],
            'unit_price' => $price,
        ]);

        return redirect()->route('cart.index')->with('status', 'Collection added to cart.');
    }

    public function updateQuantity(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeCartItem($request, $cartItem);

        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:50']]);
        $cartItem->update(['quantity' => $data['quantity']]);

        return back();
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeCartItem($request, $cartItem);
        $cartItem->delete();

        return back()->with('status', 'Item removed.');
    }

    public function applyCoupon(Request $request, CouponValidator $validator): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string']]);
        $cart = $this->cartResolver->current($request)->load('items.itemable');

        $coupon = Coupon::where('code', $data['code'])->first();

        if (! $coupon) {
            return back()->withErrors(['coupon' => 'Invalid coupon code.']);
        }

        try {
            $validator->validate($coupon, $cart, $request->user()?->id);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $cart->update(['coupon_id' => $coupon->id]);

        return back()->with('status', 'Coupon applied.');
    }

    public function removeCoupon(Request $request): RedirectResponse
    {
        $this->cartResolver->current($request)->update(['coupon_id' => null]);

        return back()->with('status', 'Coupon removed.');
    }

    protected function authorizeCartItem(Request $request, CartItem $cartItem): void
    {
        $cart = $this->cartResolver->current($request);
        abort_unless($cartItem->cart_id === $cart->id, 403);
    }
}
