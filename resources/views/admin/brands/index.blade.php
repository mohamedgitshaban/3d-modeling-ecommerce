@extends('admin.layouts.app')

@section('title', 'Brands')
@section('heading', 'Brands')

@section('content')
    <form method="POST" action="{{ route('admin.brands.store') }}" class="bg-white border border-stone-200 rounded-lg p-4 mb-6 flex gap-2 max-w-xl">
        @csrf
        <input type="text" name="name" placeholder="Brand name" required class="flex-1 border border-stone-300 rounded-md py-2 px-3 text-sm">
        <button class="bg-stone-900 text-white text-sm font-medium px-4 rounded-md">Add Brand</button>
    </form>

    <div class="bg-white rounded-lg border border-stone-200">
        <table class="w-full text-sm">
            <thead class="text-left text-stone-500 border-b border-stone-100">
                <tr><th class="p-3">Name</th><th class="p-3">Products</th><th class="p-3"></th></tr>
            </thead>
            <tbody>
                @foreach ($brands as $brand)
                    <tr class="border-b border-stone-50">
                        <td class="p-3">{{ $brand->name }}</td>
                        <td class="p-3">{{ $brand->products_count }}</td>
                        <td class="p-3 text-right">
                            <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" onsubmit="return confirm('Delete this brand?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $brands->links() }}</div>
@endsection
