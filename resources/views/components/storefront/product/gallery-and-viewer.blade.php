@props(['product', 'variant'])

@php
    $images = $variant?->galleryUrls() ?? [];
    $modelUrl = $variant?->model3dUrl('glb');
    $modelUsdz = $variant?->model3dUrl('usdz');
    $poster = $variant?->model3dPoster();
@endphp

<div x-data="{ tab: '{{ $modelUrl ? '3d' : 'photos' }}' }">
    <div class="aspect-square bg-stone-100 rounded-lg overflow-hidden relative">
        <div x-show="tab === 'photos'" x-cloak>
            @if (count($images))
                <img src="{{ $images[0] }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center text-stone-400 text-sm">No image available</div>
            @endif
        </div>

        <div x-show="tab === '3d'" x-cloak>
            @if ($modelUrl)
                <model-viewer
                    src="{{ $modelUrl }}"
                    @if ($modelUsdz) ios-src="{{ $modelUsdz }}" @endif
                    @if ($poster) poster="{{ $poster }}" @endif
                    camera-controls
                    auto-rotate
                    ar
                    shadow-intensity="1"
                    exposure="1"
                    style="width: 100%; height: 100%; background-color: #f5f5f4;"
                    alt="{{ $product->name }} — interactive 3D model">
                    <button slot="ar-button" class="absolute bottom-4 right-4 bg-stone-900 text-white text-xs font-semibold px-3 py-2 rounded-md">View in your room</button>
                </model-viewer>
            @endif
        </div>
    </div>

    <div class="mt-3 flex gap-2">
        <button @click="tab = 'photos'" :class="tab === 'photos' ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-600'" class="px-3 py-1.5 rounded-md text-sm font-medium">Photos</button>
        @if ($modelUrl)
            <button @click="tab = '3d'" :class="tab === '3d' ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-600'" class="px-3 py-1.5 rounded-md text-sm font-medium inline-flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12"/></svg>
                3D View
            </button>
        @endif
    </div>

    @if (count($images) > 1)
        <div class="mt-3 grid grid-cols-5 gap-2">
            @foreach ($images as $image)
                <img src="{{ $image }}" class="aspect-square object-cover rounded border border-stone-200">
            @endforeach
        </div>
    @endif
</div>
