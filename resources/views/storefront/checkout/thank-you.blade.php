@extends('storefront.layouts.app')

@section('title', 'Order Confirmed — FixtureCraft')

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-16 text-center">
        <div class="mx-auto h-14 w-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.4 7.4a1 1 0 01-1.4 0L3.3 9.5a1 1 0 111.4-1.4l3.6 3.6 6.7-6.7a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
        </div>
        <h1 class="text-2xl font-serif font-semibold mb-2">Thanks, {{ $order->customer_name }}!</h1>
        <p class="text-stone-600 mb-8">Your order <span class="font-medium text-stone-900">{{ $order->order_number }}</span> has been received.
            Once Paymob confirms payment we'll start processing it right away.</p>

        <div class="bg-white border border-stone-200 rounded-lg p-6 text-left mb-8">
            <div class="flex justify-between text-sm mb-2"><span class="text-stone-500">Order status</span><span class="font-medium">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span></div>
            <div class="flex justify-between text-sm mb-2"><span class="text-stone-500">Total</span><span class="font-medium">${{ number_format($order->grand_total, 2) }}</span></div>
            <div class="flex justify-between text-sm"><span class="text-stone-500">Email</span><span class="font-medium">{{ $order->customer_email }}</span></div>
        </div>

        <a href="{{ $order->publicTrackingUrl() }}" class="inline-block bg-stone-900 hover:bg-stone-800 text-white font-semibold px-6 py-3 rounded-md">Track Your Order</a>
        <p class="text-xs text-stone-500 mt-3">Bookmark this link, or look it up any time at <a href="{{ route('orders.track.create') }}" class="underline">/track-order</a> with your order number and email.</p>
    </div>
@endsection
