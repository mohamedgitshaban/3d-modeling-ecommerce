@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="grid grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg p-5 border border-stone-200">
            <div class="text-sm text-stone-500">Products</div>
            <div class="text-2xl font-semibold">{{ $stats['products'] }}</div>
        </div>
        <div class="bg-white rounded-lg p-5 border border-stone-200">
            <div class="text-sm text-stone-500">Orders Today</div>
            <div class="text-2xl font-semibold">{{ $stats['orders_today'] }}</div>
        </div>
        <div class="bg-white rounded-lg p-5 border border-stone-200">
            <div class="text-sm text-stone-500">Paid/Processing</div>
            <div class="text-2xl font-semibold">{{ $stats['pending_orders'] }}</div>
        </div>
        <div class="bg-white rounded-lg p-5 border border-stone-200">
            <div class="text-sm text-stone-500">Low Stock Items</div>
            <div class="text-2xl font-semibold text-amber-600">{{ $stats['low_stock'] }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-stone-200">
        <div class="p-4 font-semibold border-b border-stone-200">Recent Orders</div>
        <table class="w-full text-sm">
            <thead class="text-left text-stone-500 border-b border-stone-100">
                <tr><th class="p-3">Order #</th><th class="p-3">Status</th><th class="p-3">Total</th><th class="p-3">Placed</th></tr>
            </thead>
            <tbody>
                @foreach ($recentOrders as $order)
                    <tr class="border-b border-stone-50">
                        <td class="p-3"><a href="{{ route('admin.orders.show', $order) }}" class="text-amber-700 hover:underline">{{ $order->order_number }}</a></td>
                        <td class="p-3">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
                        <td class="p-3">${{ number_format($order->grand_total, 2) }}</td>
                        <td class="p-3">{{ $order->created_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
