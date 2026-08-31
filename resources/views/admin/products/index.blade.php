@extends('admin.layouts.app')

@section('title', 'Products')
@section('heading', 'Products')

@section('content')
    <div class="flex justify-between mb-4">
        <form method="GET" class="flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products..." class="border border-stone-300 rounded-md py-2 px-3 text-sm w-64">
        </form>
        <a href="{{ route('admin.products.create') }}" class="bg-stone-900 text-white text-sm font-medium px-4 py-2 rounded-md">+ New Product</a>
    </div>

    <div class="bg-white rounded-lg border border-stone-200">
        <table class="w-full text-sm">
            <thead class="text-left text-stone-500 border-b border-stone-100">
                <tr><th class="p-3">Name</th><th class="p-3">Category</th><th class="p-3">Variants</th><th class="p-3">Active</th><th class="p-3"></th></tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr class="border-b border-stone-50">
                        <td class="p-3">{{ $product->name }}</td>
                        <td class="p-3">{{ $product->category->name }}</td>
                        <td class="p-3">{{ $product->variants->count() }}</td>
                        <td class="p-3">{{ $product->is_active ? 'Yes' : 'No' }}</td>
                        <td class="p-3 text-right"><a href="{{ route('admin.products.edit', $product) }}" class="text-amber-700 hover:underline">Edit</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
@endsection
