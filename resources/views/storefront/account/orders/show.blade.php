@extends('storefront.layouts.app')

@section('title', 'Order '.$order->order_number.' — FixtureCraft')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-10" x-data x-init="window.watchOrder && window.watchOrder({{ $order->id }}, () => window.location.reload())">
        <h1 class="text-2xl font-serif font-semibold mb-2">Order {{ $order->order_number }}</h1>
        <p class="text-stone-600 mb-8">This page updates live when your order status changes.</p>

        <div class="bg-white border border-stone-200 rounded-lg p-6 mb-8">
            <x-storefront.order-timeline :order="$order" :timeline="$timeline" />
        </div>

        <div class="bg-white border border-stone-200 rounded-lg p-6">
            <h2 class="font-semibold mb-4">Items</h2>
            <ul class="divide-y divide-stone-100 text-sm">
                @foreach ($order->items as $item)
                    <li class="py-3 flex justify-between">
                        <span>{{ $item->name }} × {{ $item->quantity }}</span>
                        <span class="font-medium">${{ number_format($item->line_total, 2) }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="flex justify-between font-semibold border-t border-stone-200 pt-3 mt-3">
                <span>Total</span><span>${{ number_format($order->grand_total, 2) }}</span>
            </div>
        </div>
    </div>
@endsection
