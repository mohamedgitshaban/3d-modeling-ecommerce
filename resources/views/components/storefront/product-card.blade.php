@props(['product'])

@php
    $variant = $product->defaultVariant();
    $pricing = $variant ? app(\App\Services\Pricing\PriceResolver::class)->priceFor($variant) : null;
    $image = $variant?->galleryUrls()[0] ?? null;
@endphp

<a href="{{ route('products.show', $product) }}" class="group block bg-white rounded-lg border border-stone-200 overflow-hidden hover:shadow-lg hover:border-amber-400 transition">
    <div class="aspect-square bg-stone-100 flex items-center justify-center overflow-hidden relative">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $product->name }}" class="object-cover w-full h-full group-hover:scale-105 transition">
        @else
            <span class="text-stone-400 text-xs">No image</span>
        @endif

        @if ($variant?->getMedia('models_3d')->isNotEmpty() || $product->getMedia('models_3d')->isNotEmpty())
            <span class="absolute top-2 right-2 bg-stone-900/80 text-white text-[10px] font-semibold uppercase tracking-wide px-2 py-1 rounded">3D</span>
        @endif

        @if ($pricing && $pricing['offer'])
            <span class="absolute top-2 left-2 bg-amber-500 text-stone-900 text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded">{{ $pricing['offer']->badge_label ?? 'Sale' }}</span>
        @endif
    </div>
    <div class="p-4">
        @if ($product->brand)
            <div class="text-[11px] uppercase tracking-wide text-stone-400 mb-1">{{ $product->brand->name }}</div>
        @endif
        <div class="font-medium text-stone-800 group-hover:text-amber-700 leading-snug">{{ $product->name }}</div>
        @if ($pricing)
            <div class="mt-2 flex items-baseline gap-2">
                <span class="font-semibold text-stone-900">${{ number_format($pricing['price'], 2) }}</span>
                @if ($pricing['compare_at_price'])
                    <span class="text-sm text-stone-400 line-through">${{ number_format($pricing['compare_at_price'], 2) }}</span>
                @endif
            </div>
        @endif
    </div>
</a>
