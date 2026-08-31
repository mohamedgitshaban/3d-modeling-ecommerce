@extends('admin.layouts.app')

@section('title', 'New Product')
@section('heading', 'New Product')

@section('content')
    <form method="POST" action="{{ route('admin.products.store') }}" class="bg-white border border-stone-200 rounded-lg p-6 max-w-2xl space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Category</label>
                <select name="category_id" required class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Brand</label>
                <select name="brand_id" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                    <option value="">None</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input type="text" name="name" required class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Base SKU</label>
                <input type="text" name="base_sku" required class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">MSRP</label>
                <input type="number" step="0.01" name="msrp" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Collection Line</label>
            <input type="text" name="collection_line" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Short Description</label>
            <textarea name="short_description" rows="3" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm"></textarea>
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active</label>
        <button class="bg-stone-900 text-white text-sm font-medium px-4 py-2 rounded-md">Create Product</button>
    </form>
@endsection
