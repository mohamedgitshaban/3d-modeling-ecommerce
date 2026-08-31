@extends('storefront.layouts.app')

@section('title', 'Find a Store — FixtureCraft')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-serif font-semibold mb-2">Find a Store</h1>
        <p class="text-stone-600 mb-6">Search by city, state, or ZIP.</p>

        <form method="GET" class="mb-8">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="City, state, or ZIP" class="w-full max-w-md border border-stone-300 rounded-md py-2 px-3 text-sm">
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse ($stores as $store)
                <div class="bg-white border border-stone-200 rounded-lg p-5">
                    <div class="font-semibold text-stone-800">{{ $store->name }}</div>
                    <div class="text-sm text-stone-600 mt-1">{{ $store->toSingleLine() }}</div>
                    @if ($store->phone)
                        <div class="text-sm text-stone-600 mt-1">{{ $store->phone }}</div>
                    @endif
                </div>
            @empty
                <p class="text-stone-500">No stores found.</p>
            @endforelse
        </div>
    </div>
@endsection
