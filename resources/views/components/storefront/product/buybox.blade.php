@props(['product', 'variant', 'pricing', 'variantMap'])

@php
    $variantAttributes = $product->category->variantOptionAttributes();
    $optionChoices = [];
    foreach ($variantAttributes as $attribute) {
        $optionChoices[$attribute->label] = $product->variants
            ->map(fn ($v) => $v->optionLabels()[$attribute->label] ?? null)
            ->filter()
            ->unique()
            ->values();
    }
    $initialSelected = $variant ? $variant->optionLabels() : [];
@endphp

<div
    x-data="{
        variants: {{ $variantMap->toJson() }},
        selected: @js($initialSelected),
        quantity: 1,
        get current() {
            return this.variants.find(v => Object.keys(this.selected).every(k => v.options[k] === this.selected[k])) || null;
        },
        select(label, value) { this.selected[label] = value; },
    }"
    x-init="$watch('current', (v) => { if (v && window.watchStock) { window.watchStock(v.sku, (e) => { v.stock_status = e.status; }); } })"
>
    <div class="text-sm text-stone-500 mb-1">
        SKU: <span class="font-medium text-stone-700" x-text="current ? current.sku : '{{ $variant?->sku }}'"></span>
    </div>

    <h1 class="text-2xl md:text-3xl font-serif font-semibold text-stone-900 mb-2">{{ $product->name }}</h1>

    @if ($product->collection_line)
        <div class="text-sm text-amber-700 font-medium mb-4">{{ $product->collection_line }}</div>
    @endif

    <div class="flex items-baseline gap-3 mb-6">
        <span class="text-2xl font-semibold text-stone-900" x-text="'$' + (current ? Number(current.price).toFixed(2) : '{{ number_format($pricing['price'] ?? 0, 2) }}')"></span>
        @if ($pricing && $pricing['compare_at_price'])
            <span class="text-lg text-stone-400 line-through">${{ number_format($pricing['compare_at_price'], 2) }}</span>
        @endif
        @if ($product->msrp)
            <span class="text-xs text-stone-500">MSRP: ${{ number_format($product->msrp, 2) }}</span>
        @endif
    </div>

    @foreach ($optionChoices as $label => $values)
        <div class="mb-4">
            <div class="text-sm font-medium text-stone-700 mb-2">{{ $label }}</div>
            <div class="flex flex-wrap gap-2">
                @foreach ($values as $value)
                    <button
                        type="button"
                        @click="select('{{ $label }}', '{{ $value }}')"
                        :class="selected['{{ $label }}'] === '{{ $value }}' ? 'border-stone-900 bg-stone-900 text-white' : 'border-stone-300 text-stone-700 hover:border-stone-500'"
                        class="px-3 py-1.5 rounded-md border text-sm font-medium transition"
                    >{{ $value }}</button>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="mb-6">
        <span
            class="inline-flex items-center gap-2 text-sm font-medium"
            x-text="current && current.stock_status === 'out_of_stock' ? 'Out of stock' : (current && current.stock_status === 'low_stock' ? 'Low stock — order soon' : 'In stock')"
            :class="current && current.stock_status === 'out_of_stock' ? 'text-red-600' : (current && current.stock_status === 'low_stock' ? 'text-amber-600' : 'text-emerald-600')"
        ></span>
    </div>

    <form method="POST" action="{{ route('cart.variants.store') }}" class="flex items-center gap-3">
        @csrf
        <input type="hidden" name="variant_id" :value="current ? current.id : {{ $variant?->id ?? 'null' }}">
        <input type="number" name="quantity" x-model="quantity" min="1" max="50" class="w-20 border border-stone-300 rounded-md py-2 px-3 text-sm">
        <button
            type="submit"
            :disabled="current && current.stock_status === 'out_of_stock'"
            class="flex-1 bg-stone-900 hover:bg-stone-800 disabled:bg-stone-300 disabled:cursor-not-allowed text-white font-semibold py-3 rounded-md transition"
        >Add to Cart</button>
    </form>

    <a href="{{ route('store-locator.index') }}" class="inline-block mt-4 text-sm text-amber-700 hover:text-amber-800 font-medium">Find a Store →</a>
</div>
