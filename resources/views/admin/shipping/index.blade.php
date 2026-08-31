@extends('admin.layouts.app')

@section('title', 'Shipping')
@section('heading', 'Shipping Zones & Rates')

@section('content')
    <form method="POST" action="{{ route('admin.shipping.zones.store') }}" class="bg-white border border-stone-200 rounded-lg p-4 mb-6 flex gap-2 max-w-2xl">
        @csrf
        <input type="text" name="name" placeholder="Zone name (e.g. United States)" required class="flex-1 border border-stone-300 rounded-md py-2 px-3 text-sm">
        <input type="text" name="countries" placeholder="Countries, comma separated" required class="flex-1 border border-stone-300 rounded-md py-2 px-3 text-sm">
        <button class="bg-stone-900 text-white text-sm font-medium px-4 rounded-md">Add Zone</button>
    </form>

    @foreach ($zones as $zone)
        <div class="bg-white border border-stone-200 rounded-lg p-6 mb-6 max-w-3xl">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold">{{ $zone->name }} <span class="text-xs text-stone-400">({{ implode(', ', $zone->countries) }})</span></h2>
                <form method="POST" action="{{ route('admin.shipping.zones.destroy', $zone) }}" onsubmit="return confirm('Delete this zone?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-600 hover:underline">Delete Zone</button>
                </form>
            </div>

            <table class="w-full text-sm mb-4">
                <thead class="text-left text-stone-500 border-b border-stone-100">
                    <tr><th class="p-2">Rate</th><th class="p-2">Type</th><th class="p-2">Base</th><th class="p-2">Est. Days</th><th class="p-2"></th></tr>
                </thead>
                <tbody>
                    @foreach ($zone->rates as $rate)
                        <tr class="border-b border-stone-50">
                            <td class="p-2">{{ $rate->name }}</td>
                            <td class="p-2">{{ $rate->calculation_type }}</td>
                            <td class="p-2">${{ number_format($rate->base_rate, 2) }}</td>
                            <td class="p-2">{{ $rate->estimatedDeliveryLabel() }}</td>
                            <td class="p-2 text-right">
                                <form method="POST" action="{{ route('admin.shipping.rates.destroy', [$zone, $rate]) }}">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-600 hover:underline">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <form method="POST" action="{{ route('admin.shipping.rates.store', $zone) }}" class="flex flex-wrap gap-2 items-center text-xs">
                @csrf
                <input type="text" name="name" placeholder="Rate name" required class="border border-stone-300 rounded-md py-1.5 px-2 w-32">
                <select name="calculation_type" class="border border-stone-300 rounded-md py-1.5 px-2">
                    <option value="flat">Flat</option>
                    <option value="weight_based">Weight-based</option>
                    <option value="free_over_threshold">Free over threshold</option>
                </select>
                <input type="number" step="0.01" name="base_rate" placeholder="Base rate" required class="border border-stone-300 rounded-md py-1.5 px-2 w-24">
                <input type="number" step="0.01" name="rate_per_kg" placeholder="Per kg" class="border border-stone-300 rounded-md py-1.5 px-2 w-20">
                <input type="number" step="0.01" name="free_over_amount" placeholder="Free over $" class="border border-stone-300 rounded-md py-1.5 px-2 w-24">
                <input type="number" name="estimated_days_min" placeholder="Min days" class="border border-stone-300 rounded-md py-1.5 px-2 w-20">
                <input type="number" name="estimated_days_max" placeholder="Max days" class="border border-stone-300 rounded-md py-1.5 px-2 w-20">
                <button class="bg-stone-100 text-stone-800 font-medium px-3 py-1.5 rounded-md">Add Rate</button>
            </form>
        </div>
    @endforeach
@endsection
