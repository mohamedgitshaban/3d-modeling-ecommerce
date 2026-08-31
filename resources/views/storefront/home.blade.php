@extends('storefront.layouts.app')

@section('title', 'FixtureCraft — Bath & Kitchen Fixtures in 3D')

@section('content')
    <section class="bg-gradient-to-br from-stone-900 to-stone-800 text-white">
        <div class="max-w-7xl mx-auto px-4 py-24 grid md:grid-cols-2 gap-10 items-center">
            <div>
                <p class="uppercase tracking-widest text-amber-400 text-xs font-semibold mb-4">New — Interactive 3D previews</p>
                <h1 class="text-4xl md:text-5xl font-serif font-semibold leading-tight mb-6">Fixtures you can turn around in your hand — before they arrive.</h1>
                <p class="text-stone-300 mb-8 max-w-lg">Every faucet, vanity, and mirror rendered in true 3D. Pick your finish, see it rotate, then buy it as part of a complete matched suite.</p>
                <a href="{{ route('categories.show', \App\Models\Category::whereNull('parent_id')->first()) }}" class="inline-block bg-amber-500 hover:bg-amber-400 text-stone-900 font-semibold px-6 py-3 rounded-md transition">Shop Bathroom Fixtures</a>
            </div>
            <div class="rounded-xl overflow-hidden shadow-2xl bg-stone-800 aspect-square flex items-center justify-center text-stone-500">
                <span class="text-sm">3D product viewer preview</span>
            </div>
        </div>
    </section>

    @if ($activeOffers->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 py-6 flex flex-wrap gap-3">
            @foreach ($activeOffers as $offer)
                <span class="inline-flex items-center gap-2 bg-amber-100 text-amber-900 text-sm font-semibold px-4 py-2 rounded-full">
                    {{ $offer->badge_label ?? $offer->name }} · {{ $offer->name }}
                </span>
            @endforeach
        </section>
    @endif

    <section class="max-w-7xl mx-auto px-4 py-12">
        <h2 class="text-2xl font-serif font-semibold mb-6">Shop by Category</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach ($featuredCategories as $category)
                <a href="{{ route('categories.show', $category) }}" class="group block bg-white rounded-lg border border-stone-200 p-6 text-center hover:border-amber-500 hover:shadow-md transition">
                    <div class="font-medium text-stone-800 group-hover:text-amber-700">{{ $category->name }}</div>
                </a>
            @endforeach
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 py-12">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-serif font-semibold">Featured Products</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @foreach ($featuredProducts as $product)
                <x-storefront.product-card :product="$product" />
            @endforeach
        </div>
    </section>

    <section class="bg-stone-900 text-white">
        <div class="max-w-7xl mx-auto px-4 py-14 text-center">
            <h2 class="text-2xl font-serif font-semibold mb-3">Build a Complete Suite</h2>
            <p class="text-stone-300 max-w-xl mx-auto mb-6">Mix a vanity, faucet, and mirror from different categories into one matched collection — priced and shipped together.</p>
            <a href="{{ route('categories.show', \App\Models\Category::whereNull('parent_id')->first()) }}" class="inline-block bg-white text-stone-900 font-semibold px-6 py-3 rounded-md hover:bg-stone-100 transition">Explore Collections</a>
        </div>
    </section>
@endsection
