@extends('admin.layouts.app')

@section('title', 'Orders')
@section('heading', 'Orders')

@section('content')
    <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Order # or email" class="border border-stone-300 rounded-md py-2 px-3 text-sm w-64">
        <select name="status" onchange="this.form.submit()" class="border border-stone-300 rounded-md py-2 px-3 text-sm">
            <option value="">All statuses</option>
            @foreach (['pending','awaiting_payment','paid','processing','shipped','delivered','cancelled','refunded'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>
            @endforeach
        </select>
    </form>

    <div class="bg-white rounded-lg border border-stone-200">
        <table class="w-full text-sm">
            <thead class="text-left text-stone-500 border-b border-stone-100">
                <tr><th class="p-3">Order #</th><th class="p-3">Customer</th><th class="p-3">Status</th><th class="p-3">Total</th><th class="p-3">Placed</th><th class="p-3"></th></tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr class="border-b border-stone-50">
                        <td class="p-3">{{ $order->order_number }}</td>
                        <td class="p-3">{{ $order->customer_name }}<br><span class="text-xs text-stone-400">{{ $order->customer_email }}</span></td>
                        <td class="p-3">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
                        <td class="p-3">${{ number_format($order->grand_total, 2) }}</td>
                        <td class="p-3">{{ $order->created_at->format('M j, Y') }}</td>
                        <td class="p-3 text-right"><a href="{{ route('admin.orders.show', $order) }}" class="text-amber-700 hover:underline">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
