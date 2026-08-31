@extends('storefront.layouts.app')

@section('title', $collection->name.' — FixtureCraft Collections')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-10">
        <h1 class="text-3xl font-serif font-semibold mb-2">{{ $collection->name }}</h1>
        <p class="text-stone-600 mb-8 max-w-2xl">{{ $collection->description }}</p>

        <form method="GET" class="space-y-8">
            @foreach ($collection->slots as $slot)
                @php $eligible = $slot->eligibleProducts(); @endphp
                <div class="bg-white border border-stone-200 rounded-lg p-6">
                    <h2 class="font-semibold text-stone-800 mb-4">{{ $slot->label }} @if(!$slot->is_required)<span class="text-stone-400 font-normal text-sm">(optional)</span>@endif</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach ($eligible as $product)
                            @foreach ($product->variants as $productVariant)
                                <label class="border rounded-md p-3 cursor-pointer flex items-center gap-3 {{ (int) $selection[$slot->id] === $productVariant->id ? 'border-stone-900 ring-1 ring-stone-900' : 'border-stone-200' }}">
                                    <input type="radio" name="slot_{{ $slot->id }}" value="{{ $productVariant->id }}" onchange="this.form.submit()" {{ (int) $selection[$slot->id] === $productVariant->id ? 'checked' : '' }} class="accent-stone-900">
                                    <span class="text-sm">
                                        <span class="block font-medium text-stone-800">{{ $product->name }}</span>
                                        <span class="block text-stone-500">{{ implode(' / ', $productVariant->optionLabels()) ?: $productVariant->sku }} — ${{ number_format($productVariant->price, 2) }}</span>
                                    </span>
                                </label>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @endforeach
        </form>

        <div class="mt-8 bg-stone-900 text-white rounded-lg p-6 flex items-center justify-between">
            <div>
                <div class="text-sm text-stone-300">Suite Price @if($collection->discount_percent)(includes {{ $collection->discount_percent }}% suite discount)@endif</div>
                <div class="text-3xl font-semibold">${{ number_format($price, 2) }}</div>
            </div>
            <form method="POST" action="{{ route('cart.collections.store') }}">
                @csrf
                <input type="hidden" name="collection_id" value="{{ $collection->id }}">
                @foreach ($selection as $slotId => $variantId)
                    <input type="hidden" name="selection[{{ $slotId }}]" value="{{ $variantId }}">
                @endforeach
                <button class="bg-amber-500 hover:bg-amber-400 text-stone-900 font-semibold px-6 py-3 rounded-md">Add Collection to Cart</button>
            </form>
        </div>
    </div>
@endsection
