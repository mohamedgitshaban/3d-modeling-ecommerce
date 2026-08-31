@extends('storefront.layouts.app')

@section('title', 'Checkout — FixtureCraft')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-serif font-semibold mb-8">Checkout</h1>

        <form method="POST" action="{{ route('checkout.store') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            @csrf

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white border border-stone-200 rounded-lg p-6">
                    <h2 class="font-semibold mb-4">Contact</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" name="customer_name" placeholder="Full name" required value="{{ old('customer_name') }}" class="col-span-2 border border-stone-300 rounded-md py-2 px-3 text-sm">
                        <input type="email" name="customer_email" placeholder="Email" required value="{{ old('customer_email') }}" class="border border-stone-300 rounded-md py-2 px-3 text-sm">
                        <input type="text" name="customer_phone" placeholder="Phone" value="{{ old('customer_phone') }}" class="border border-stone-300 rounded-md py-2 px-3 text-sm">
                    </div>
                </div>

                <div class="bg-white border border-stone-200 rounded-lg p-6">
                    <h2 class="font-semibold mb-4">Shipping Address</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" name="address_line_1" placeholder="Address line 1" required class="col-span-2 border border-stone-300 rounded-md py-2 px-3 text-sm">
                        <input type="text" name="address_line_2" placeholder="Address line 2" class="col-span-2 border border-stone-300 rounded-md py-2 px-3 text-sm">
                        <input type="text" name="city" placeholder="City" required class="border border-stone-300 rounded-md py-2 px-3 text-sm">
                        <input type="text" name="state" placeholder="State" class="border border-stone-300 rounded-md py-2 px-3 text-sm">
                        <input type="text" name="postal_code" placeholder="Postal code" class="border border-stone-300 rounded-md py-2 px-3 text-sm">
                        <input type="text" name="country" placeholder="Country" required value="United States" class="border border-stone-300 rounded-md py-2 px-3 text-sm">
                    </div>
                </div>

                <div class="bg-white border border-stone-200 rounded-lg p-6">
                    <h2 class="font-semibold mb-4">Shipping Method</h2>
                    <div class="space-y-2">
                        @foreach ($shippingZones as $zone)
                            @foreach ($zone->rates as $rate)
                                <label class="flex items-center justify-between border border-stone-200 rounded-md p-3 cursor-pointer">
                                    <span class="flex items-center gap-3">
                                        <input type="radio" name="shipping_rate_id" value="{{ $rate->id }}" required class="accent-stone-900">
                                        <span>
                                            <span class="block font-medium text-sm">{{ $rate->name }}</span>
                                            <span class="block text-xs text-stone-500">{{ $rate->estimatedDeliveryLabel() }}</span>
                                        </span>
                                    </span>
                                    <span class="font-medium text-sm">${{ number_format($rate->base_rate, 2) }}</span>
                                </label>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <div class="bg-white border border-stone-200 rounded-lg p-6">
                    <h2 class="font-semibold mb-4">Payment — Paymob</h2>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm"><input type="radio" name="payment_method" value="card" checked class="accent-stone-900"> Credit / Debit Card</label>
                        <label class="flex items-center gap-2 text-sm"><input type="radio" name="payment_method" value="wallet" class="accent-stone-900"> Mobile Wallet</label>
                        <label class="flex items-center gap-2 text-sm"><input type="radio" name="payment_method" value="kiosk" class="accent-stone-900"> Kiosk (Cash)</label>
                    </div>
                    <p class="text-xs text-stone-500 mt-3">You'll be redirected to Paymob's secure checkout to complete payment.</p>
                </div>
            </div>

            <div class="bg-white border border-stone-200 rounded-lg p-6 h-fit">
                <h2 class="font-semibold mb-4">Order Summary</h2>
                <ul class="text-sm space-y-2 mb-4">
                    @foreach ($cart->items as $item)
                        <li class="flex justify-between"><span>{{ $item->displayName() }} × {{ $item->quantity }}</span><span>${{ number_format($item->lineTotal(), 2) }}</span></li>
                    @endforeach
                </ul>
                <div class="flex justify-between font-semibold text-base border-t border-stone-200 pt-2">
                    <span>Subtotal</span><span>${{ number_format($cart->subtotal(), 2) }}</span>
                </div>
                <button type="submit" class="w-full mt-6 bg-amber-500 hover:bg-amber-400 text-stone-900 font-semibold py-3 rounded-md">Pay with Paymob</button>
            </div>
        </form>
    </div>
@endsection
