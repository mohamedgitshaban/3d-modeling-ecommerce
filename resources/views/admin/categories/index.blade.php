@extends('admin.layouts.app')

@section('title', 'Categories')
@section('heading', 'Categories')

@section('content')
    <a href="{{ route('admin.categories.create') }}" class="inline-block mb-4 bg-stone-900 text-white text-sm font-medium px-4 py-2 rounded-md">+ New Category</a>

    <div class="bg-white rounded-lg border border-stone-200">
        <table class="w-full text-sm">
            <thead class="text-left text-stone-500 border-b border-stone-100">
                <tr><th class="p-3">Name</th><th class="p-3">Parent</th><th class="p-3">Active</th><th class="p-3"></th></tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr class="border-b border-stone-50">
                        <td class="p-3">{{ $category->name }}</td>
                        <td class="p-3">{{ $category->parent->name ?? '—' }}</td>
                        <td class="p-3">{{ $category->is_active ? 'Yes' : 'No' }}</td>
                        <td class="p-3 text-right"><a href="{{ route('admin.categories.edit', $category) }}" class="text-amber-700 hover:underline">Edit</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $categories->links() }}</div>
@endsection
