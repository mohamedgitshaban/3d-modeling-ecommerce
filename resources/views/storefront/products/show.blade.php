@extends('storefront.layouts.app')

@section('title', $product->name.' — '.($product->brand->name ?? 'FixtureCraft'))
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->short_description ?? ''), 155))

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6">
        <x-storefront.breadcrumbs :trail="$product->breadcrumbTrail()" />

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-10">
            <x-storefront.product.gallery-and-viewer :product="$product" :variant="$variant" />
            <x-storefront.product.buybox :product="$product" :variant="$variant" :pricing="$pricing" :variant-map="$variantMap" />
        </div>

        <div class="mt-4 text-sm">
            <a href="https://www.example.com/where-to-buy" class="text-amber-700 hover:text-amber-800 font-medium">FIND A STORE</a>
        </div>

        <div class="mt-10 max-w-4xl">
            @foreach ($specSections as $section)
                <x-storefront.product.spec-section :group="$section['group']" :items="$section['items']" />
            @endforeach

            @if ($product->warrantyDocuments()->isNotEmpty())
                <div class="border-t border-stone-200 py-6">
                    <h3 class="text-lg font-serif font-semibold text-stone-900 mb-4">Documents</h3>
                    <ul class="space-y-2">
                        @foreach ($product->warrantyDocuments() as $doc)
                            <li><a href="{{ $doc->getUrl() }}" target="_blank" class="text-amber-700 hover:text-amber-800 text-sm font-medium">{{ $doc->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection
