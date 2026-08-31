@extends('admin.layouts.app')

@section('title', 'New Collection')
@section('heading', 'New Collection')

@section('content')
    <form method="POST" action="{{ route('admin.collections.store') }}" class="bg-white border border-stone-200 rounded-lg p-6 max-w-2xl space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input type="text" name="name" required class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm"></textarea>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Pricing Mode</label>
                <select name="pricing_mode" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                    <option value="sum_of_selections">Sum of Selections</option>
                    <option value="fixed">Fixed Price</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Fixed Price</label>
                <input type="number" step="0.01" name="fixed_price" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Discount % (sum mode)</label>
                <input type="number" step="0.01" name="discount_percent" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active</label>
        <button class="bg-stone-900 text-white text-sm font-medium px-4 py-2 rounded-md">Create Collection</button>
    </form>
@endsection
