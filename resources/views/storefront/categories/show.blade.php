@extends('storefront.layouts.app')

@section('title', $category->name.' — FixtureCraft')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($category->description ?? ''), 155))

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6">
        <x-storefront.breadcrumbs :trail="$category->breadcrumbTrail()" />

        <div class="mt-4 mb-8">
            <h1 class="text-3xl font-serif font-semibold">{{ $category->name }}</h1>
            @if ($category->description)
                <p class="text-stone-600 mt-2 max-w-3xl">{{ $category->description }}</p>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <aside class="md:col-span-1">
                <h2 class="font-semibold text-stone-800 mb-3">Filter By</h2>
                <form method="GET" class="space-y-5">
                    @foreach ($filterableAttributes as $attribute)
                        <div>
                            <div class="text-sm font-medium text-stone-700 mb-2">{{ $attribute->label }}</div>
                            <select name="{{ $attribute->key }}" onchange="this.form.submit()" class="w-full border border-stone-300 rounded-md text-sm py-1.5 px-2">
                                <option value="">Any</option>
                                @foreach (($attribute->options ?? []) as $option)
                                    <option value="{{ $option }}" @selected(request($attribute->key) === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </form>
            </aside>

            <div class="md:col-span-3">
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse ($products as $product)
                        <x-storefront.product-card :product="$product" />
                    @empty
                        <p class="text-stone-500 col-span-full">No products match those filters yet.</p>
                    @endforelse
                </div>

                <div class="mt-8">{{ $products->links() }}</div>
            </div>
        </div>
    </div>
@endsection
