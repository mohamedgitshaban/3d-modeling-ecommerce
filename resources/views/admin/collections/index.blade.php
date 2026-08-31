@extends('admin.layouts.app')

@section('title', 'Collections')
@section('heading', 'Collections')

@section('content')
    <a href="{{ route('admin.collections.create') }}" class="inline-block mb-4 bg-stone-900 text-white text-sm font-medium px-4 py-2 rounded-md">+ New Collection</a>

    <div class="bg-white rounded-lg border border-stone-200">
        <table class="w-full text-sm">
            <thead class="text-left text-stone-500 border-b border-stone-100">
                <tr><th class="p-3">Name</th><th class="p-3">Pricing</th><th class="p-3">Slots</th><th class="p-3">Active</th><th class="p-3"></th></tr>
            </thead>
            <tbody>
                @foreach ($collections as $collection)
                    <tr class="border-b border-stone-50">
                        <td class="p-3">{{ $collection->name }}</td>
                        <td class="p-3">{{ $collection->pricing_mode }}</td>
                        <td class="p-3">{{ $collection->slots_count }}</td>
                        <td class="p-3">{{ $collection->is_active ? 'Yes' : 'No' }}</td>
                        <td class="p-3 text-right"><a href="{{ route('admin.collections.edit', $collection) }}" class="text-amber-700 hover:underline">Edit</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $collections->links() }}</div>
@endsection
