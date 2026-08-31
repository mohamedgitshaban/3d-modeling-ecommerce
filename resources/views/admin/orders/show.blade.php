@extends('admin.layouts.app')

@section('title', 'Order '.$order->order_number)
@section('heading', 'Order '.$order->order_number)

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-stone-200 rounded-lg p-6">
                <h2 class="font-semibold mb-4">Tracking Timeline</h2>
                <x-storefront.order-timeline :order="$order" :timeline="$timeline" />
            </div>

            <div class="bg-white border border-stone-200 rounded-lg p-6">
                <h2 class="font-semibold mb-4">Items</h2>
                <table class="w-full text-sm">
                    <thead class="text-left text-stone-500 border-b border-stone-100">
                        <tr><th class="p-2">Item</th><th class="p-2">SKU</th><th class="p-2">Qty</th><th class="p-2">Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr class="border-b border-stone-50">
                                <td class="p-2">{{ $item->name }}</td>
                                <td class="p-2">{{ $item->sku }}</td>
                                <td class="p-2">{{ $item->quantity }}</td>
                                <td class="p-2">${{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-white border border-stone-200 rounded-lg p-6">
                <h2 class="font-semibold mb-4">Status History</h2>
                <ul class="text-sm space-y-2">
                    @foreach ($order->statusHistories as $history)
                        <li class="flex justify-between border-b border-stone-50 pb-2">
                            <span>{{ ucfirst(str_replace('_', ' ', $history->status)) }} @if($history->note) — {{ $history->note }} @endif</span>
                            <span class="text-stone-400">{{ $history->created_at->format('M j, Y g:ia') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white border border-stone-200 rounded-lg p-6">
                <h2 class="font-semibold mb-2">Customer</h2>
                <p class="text-sm">{{ $order->customer_name }}<br>{{ $order->customer_email }}<br>{{ $order->customer_phone }}</p>
                @if ($order->shippingAddress)
                    <h3 class="font-medium text-sm mt-4 mb-1">Shipping Address</h3>
                    <p class="text-sm text-stone-600">{{ $order->shippingAddress->toSingleLine() }}</p>
                @endif
            </div>

            <div class="bg-white border border-stone-200 rounded-lg p-6">
                <h2 class="font-semibold mb-4">Update Status</h2>
                <form method="POST" action="{{ route('admin.orders.status.update', $order) }}" class="space-y-2">
                    @csrf
                    <select name="status" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                        <option value="processing">Processing</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="refunded">Refunded</option>
                    </select>
                    <input type="text" name="note" placeholder="Note (optional)" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                    <button class="w-full bg-stone-900 text-white text-sm font-medium py-2 rounded-md">Update Status</button>
                </form>
            </div>

            <div class="bg-white border border-stone-200 rounded-lg p-6">
                <h2 class="font-semibold mb-1">Mark Shipped</h2>
                <p class="text-xs text-stone-500 mb-3">This is what powers the customer's order-tracking page.</p>
                <form method="POST" action="{{ route('admin.orders.ship', $order) }}" class="space-y-2">
                    @csrf
                    <input type="text" name="carrier" value="{{ $order->carrier }}" placeholder="Carrier (e.g. UPS)" required class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                    <input type="text" name="tracking_number" value="{{ $order->tracking_number }}" placeholder="Tracking Number" required class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                    <input type="url" name="carrier_tracking_url" value="{{ $order->carrier_tracking_url }}" placeholder="Carrier tracking URL (optional)" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                    <button class="w-full bg-amber-500 hover:bg-amber-400 text-stone-900 text-sm font-semibold py-2 rounded-md">Mark Shipped</button>
                </form>
            </div>

            <div class="bg-white border border-stone-200 rounded-lg p-6">
                <h2 class="font-semibold mb-2">Payments</h2>
                @foreach ($order->payments as $payment)
                    <div class="text-sm border-b border-stone-50 pb-2 mb-2">
                        <div class="flex justify-between"><span>{{ $payment->gateway }} ({{ $payment->method }})</span><span>{{ $payment->status }}</span></div>
                        <div class="text-xs text-stone-400">Txn: {{ $payment->transaction_id ?? '—' }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
