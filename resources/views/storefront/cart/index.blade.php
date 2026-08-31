@extends('storefront.layouts.app')

@section('title', 'Your Cart — FixtureCraft')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-serif font-semibold mb-8">Your Cart</h1>

        @if ($cart->items->isEmpty())
            <p class="text-stone-500">Your cart is empty. <a href="{{ route('home') }}" class="text-amber-700 hover:underline">Continue shopping</a>.</p>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    @foreach ($cart->items as $item)
                        <div class="flex items-center gap-4 bg-white border border-stone-200 rounded-lg p-4">
                            <div class="flex-1">
                                <div class="font-medium text-stone-800">{{ $item->displayName() }}</div>
                                @if ($item->itemable instanceof \App\Models\ProductVariant)
                                    <div class="text-sm text-stone-500">{{ implode(' / ', $item->itemable->optionLabels()) }} — SKU: {{ $item->itemable->sku }}</div>
                                @endif
                                <form method="POST" action="{{ route('cart.items.update', $item) }}" class="mt-2 flex items-center gap-2">
                                    @csrf @method('PATCH')
                                    <label class="text-xs text-stone-500">Qty</label>
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="50" class="w-16 border border-stone-300 rounded-md py-1 px-2 text-sm" onchange="this.form.submit()">
                                </form>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-stone-900">${{ number_format($item->lineTotal(), 2) }}</div>
                                <form method="POST" action="{{ route('cart.items.destroy', $item) }}" class="mt-2">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-600 hover:underline">Remove</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="bg-white border border-stone-200 rounded-lg p-6 h-fit">
                    <h2 class="font-semibold mb-4">Order Summary</h2>

                    <form method="POST" action="{{ route('cart.coupon.apply') }}" class="flex gap-2 mb-4">
                        @csrf
                        <input type="text" name="code" placeholder="Coupon code" value="{{ $cart->coupon->code ?? '' }}" class="flex-1 border border-stone-300 rounded-md py-2 px-3 text-sm">
                        <button class="bg-stone-900 text-white text-sm font-medium px-4 rounded-md">Apply</button>
                    </form>
                    @if ($cart->coupon)
                        <form method="POST" action="{{ route('cart.coupon.remove') }}" class="mb-4">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-600 hover:underline">Remove coupon "{{ $cart->coupon->code }}"</button>
                        </form>
                    @endif

                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt>Subtotal</dt><dd>${{ number_format($totals['subtotal'], 2) }}</dd></div>
                        <div class="flex justify-between text-emerald-700"><dt>Discount</dt><dd>-${{ number_format($totals['discount_total'], 2) }}</dd></div>
                        <div class="flex justify-between"><dt>Shipping</dt><dd>calculated at checkout</dd></div>
                        <div class="flex justify-between font-semibold text-base border-t border-stone-200 pt-2 mt-2"><dt>Total</dt><dd>${{ number_format($totals['subtotal'] - $totals['discount_total'], 2) }}</dd></div>
                    </dl>

                    <a href="{{ route('checkout.show') }}" class="block text-center mt-6 bg-amber-500 hover:bg-amber-400 text-stone-900 font-semibold py-3 rounded-md">Proceed to Checkout</a>
                </div>
            </div>
        @endif
    </div>
@endsection
