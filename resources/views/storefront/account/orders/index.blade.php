@extends('storefront.layouts.app')

@section('title', 'My Orders — FixtureCraft')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-serif font-semibold mb-8">My Orders</h1>

        <div class="bg-white border border-stone-200 rounded-lg divide-y divide-stone-100">
            @forelse ($orders as $order)
                <a href="{{ route('account.orders.show', $order) }}" class="flex items-center justify-between p-4 hover:bg-stone-50">
                    <div>
                        <div class="font-medium text-stone-800">{{ $order->order_number }}</div>
                        <div class="text-sm text-stone-500">{{ $order->created_at->format('M j, Y') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-medium">${{ number_format($order->grand_total, 2) }}</div>
                        <div class="text-sm text-stone-500">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</div>
                    </div>
                </a>
            @empty
                <p class="p-4 text-stone-500">You haven't placed any orders yet.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    </div>
@endsection
