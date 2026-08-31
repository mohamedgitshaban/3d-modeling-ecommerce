@extends('admin.layouts.app')

@section('title', 'New Offer')
@section('heading', 'New Offer')

@section('content')
    <form method="POST" action="{{ route('admin.offers.store') }}" class="bg-white border border-stone-200 rounded-lg p-6 max-w-2xl space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input type="text" name="name" required class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Type</label>
                <select name="type" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                    <option value="percentage_off">Percentage Off</option>
                    <option value="fixed_off">Fixed Amount Off</option>
                    <option value="bundle_discount">Bundle Discount</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Value</label>
                <input type="number" step="0.01" name="value" required class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Target Type</label>
                <select name="target_type" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                    <option value="category">Category</option>
                    <option value="product">Product</option>
                    <option value="collection">Collection</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Target</label>
                <select name="target_id" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                    <optgroup label="Categories">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Starts At</label>
                <input type="datetime-local" name="starts_at" required class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Ends At</label>
                <input type="datetime-local" name="ends_at" required class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Badge Label</label>
            <input type="text" name="badge_label" placeholder="e.g. 20% OFF" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active</label>
        <button class="bg-stone-900 text-white text-sm font-medium px-4 py-2 rounded-md">Create Offer</button>
    </form>
@endsection
