@extends('admin.layouts.app')

@section('title', 'Stock')
@section('heading', 'Stock')

@section('content')
    <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search SKU..." class="border border-stone-300 rounded-md py-2 px-3 text-sm w-64">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="low_stock" value="1" @checked(request('low_stock')) onchange="this.form.submit()"> Low stock only</label>
    </form>

    <div class="bg-white rounded-lg border border-stone-200">
        <table class="w-full text-sm">
            <thead class="text-left text-stone-500 border-b border-stone-100">
                <tr><th class="p-3">SKU</th><th class="p-3">Product</th><th class="p-3">Channel</th><th class="p-3">On Hand</th><th class="p-3">Reserved</th><th class="p-3">Available</th><th class="p-3">Threshold</th></tr>
            </thead>
            <tbody>
                @foreach ($stockItems as $item)
                    <tr class="border-b border-stone-50 {{ $item->quantityAvailable() <= $item->low_stock_threshold ? 'bg-amber-50' : '' }}">
                        <td class="p-3">{{ $item->variant->sku }}</td>
                        <td class="p-3">{{ $item->variant->product->name }}</td>
                        <td class="p-3">{{ $item->salesChannel->name }}</td>
                        <td class="p-3">{{ $item->quantity_on_hand }}</td>
                        <td class="p-3">{{ $item->quantity_reserved }}</td>
                        <td class="p-3 font-medium">{{ $item->quantityAvailable() }}</td>
                        <td class="p-3">
                            <form method="POST" action="{{ route('admin.stock.threshold.update', $item) }}" class="flex items-center gap-1">
                                @csrf @method('PUT')
                                <input type="number" name="low_stock_threshold" value="{{ $item->low_stock_threshold }}" class="w-16 border border-stone-300 rounded-md py-1 px-2 text-xs">
                                <button class="text-xs text-amber-700 hover:underline">Save</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $stockItems->links() }}</div>
@endsection
