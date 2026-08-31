@extends('storefront.layouts.app')

@section('title', 'Order '.$order->order_number.' — FixtureCraft')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-10" data-order-status="{{ $order->status }}" data-status-url="{{ route('orders.track.status', ['order' => $order->order_number, 'token' => $order->tracking_token]) }}">
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-2xl font-serif font-semibold">Order {{ $order->order_number }}</h1>
            <span class="text-sm text-stone-500">Placed {{ $order->created_at->format('F j, Y') }}</span>
        </div>
        <p class="text-stone-600 mb-8">Status updates automatically — no need to refresh.</p>

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

        @if ($order->shippingAddress)
            <div class="bg-white border border-stone-200 rounded-lg p-6 mt-8 text-sm">
                <h2 class="font-semibold mb-2">Shipping To</h2>
                <p class="text-stone-600">{{ $order->shippingAddress->full_name }}<br>{{ $order->shippingAddress->toSingleLine() }}</p>
            </div>
        @endif
    </div>

    @push('head')
    <script>
        // Poll the tracking-status endpoint for live updates so a guest customer
        // sees a carrier scan or delivery confirmation without refreshing.
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.querySelector('[data-status-url]');
            if (! root) return;

            const statusUrl = root.dataset.statusUrl;
            const currentStatus = root.dataset.orderStatus;

            setInterval(async () => {
                try {
                    const response = await fetch(statusUrl, { headers: { Accept: 'application/json' } });
                    const data = await response.json();
                    if (data.status !== currentStatus) {
                        window.location.reload();
                    }
                } catch (e) { /* silent — will retry on next tick */ }
            }, 20000);
        });
    </script>
    @endpush
@endsection
