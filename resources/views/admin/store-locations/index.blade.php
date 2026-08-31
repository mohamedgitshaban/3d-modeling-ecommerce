@extends('admin.layouts.app')

@section('title', 'Store Locations')
@section('heading', 'Store Locations')

@section('content')
    <form method="POST" action="{{ route('admin.store-locations.store') }}" class="bg-white border border-stone-200 rounded-lg p-4 mb-6 grid grid-cols-3 gap-2 max-w-3xl">
        @csrf
        <input type="text" name="name" placeholder="Store name" required class="border border-stone-300 rounded-md py-2 px-3 text-sm">
        <input type="text" name="address_line_1" placeholder="Address" required class="border border-stone-300 rounded-md py-2 px-3 text-sm">
        <input type="text" name="city" placeholder="City" required class="border border-stone-300 rounded-md py-2 px-3 text-sm">
        <input type="text" name="state" placeholder="State" class="border border-stone-300 rounded-md py-2 px-3 text-sm">
        <input type="text" name="postal_code" placeholder="Postal Code" class="border border-stone-300 rounded-md py-2 px-3 text-sm">
        <input type="text" name="country" placeholder="Country" required value="United States" class="border border-stone-300 rounded-md py-2 px-3 text-sm">
        <input type="text" name="phone" placeholder="Phone" class="border border-stone-300 rounded-md py-2 px-3 text-sm">
        <button class="bg-stone-900 text-white text-sm font-medium px-4 rounded-md col-span-3">Add Store</button>
    </form>

    <div class="bg-white rounded-lg border border-stone-200">
        <table class="w-full text-sm">
            <thead class="text-left text-stone-500 border-b border-stone-100">
                <tr><th class="p-3">Name</th><th class="p-3">Address</th><th class="p-3"></th></tr>
            </thead>
            <tbody>
                @foreach ($stores as $store)
                    <tr class="border-b border-stone-50">
                        <td class="p-3">{{ $store->name }}</td>
                        <td class="p-3">{{ $store->toSingleLine() }}</td>
                        <td class="p-3 text-right">
                            <form method="POST" action="{{ route('admin.store-locations.destroy', $store) }}" onsubmit="return confirm('Delete this store?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $stores->links() }}</div>
@endsection
