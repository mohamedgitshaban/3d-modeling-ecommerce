@extends('admin.layouts.app')

@section('title', 'Edit '.$collection->name)
@section('heading', 'Edit Collection: '.$collection->name)

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <form method="POST" action="{{ route('admin.collections.update', $collection) }}" class="bg-white border border-stone-200 rounded-lg p-6 space-y-4 h-fit">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" required value="{{ $collection->name }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">{{ $collection->description }}</textarea>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Pricing Mode</label>
                    <select name="pricing_mode" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                        <option value="sum_of_selections" @selected($collection->pricing_mode === 'sum_of_selections')>Sum of Selections</option>
                        <option value="fixed" @selected($collection->pricing_mode === 'fixed')>Fixed Price</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Fixed Price</label>
                    <input type="number" step="0.01" name="fixed_price" value="{{ $collection->fixed_price }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Discount %</label>
                    <input type="number" step="0.01" name="discount_percent" value="{{ $collection->discount_percent }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($collection->is_active)> Active</label>
            <button class="bg-stone-900 text-white text-sm font-medium px-4 py-2 rounded-md">Save</button>
        </form>

        <div class="bg-white border border-stone-200 rounded-lg p-6 h-fit">
            <h2 class="font-semibold mb-1">Slots</h2>
            <p class="text-xs text-stone-500 mb-4">Each slot lets the shopper pick any product from that category — this is how the collection spans multiple categories as one purchasable item.</p>

            <form method="POST" action="{{ route('admin.collections.slots.store', $collection) }}" class="space-y-2 mb-6">
                @csrf
                <input type="text" name="label" placeholder="Label (e.g. Choose your Faucet)" required class="w-full border border-stone-300 rounded-md py-1.5 px-2 text-sm">
                <select name="category_id" required class="w-full border border-stone-300 rounded-md py-1.5 px-2 text-sm">
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <label class="text-xs flex items-center gap-1"><input type="checkbox" name="is_required" value="1" checked> Required</label>
                <button class="bg-stone-100 text-stone-800 text-xs font-medium px-3 py-1.5 rounded-md">Add Slot</button>
            </form>

            <ul class="space-y-2">
                @foreach ($collection->slots as $slot)
                    <li class="flex items-center justify-between border border-stone-200 rounded-md p-3 text-sm">
                        <span>{{ $slot->label }} <span class="text-stone-400">({{ $slot->category->name }})</span></span>
                        <form method="POST" action="{{ route('admin.collections.slots.destroy', [$collection, $slot]) }}">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-600 hover:underline">Remove</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
