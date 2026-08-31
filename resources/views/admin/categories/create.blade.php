@extends('admin.layouts.app')

@section('title', 'New Category')
@section('heading', 'New Category')

@section('content')
    <form method="POST" action="{{ route('admin.categories.store') }}" class="bg-white border border-stone-200 rounded-lg p-6 max-w-xl space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Parent Category</label>
            <select name="parent_id" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                <option value="">None (top-level)</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input type="text" name="name" required class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm"></textarea>
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active</label>
        <button class="bg-stone-900 text-white text-sm font-medium px-4 py-2 rounded-md">Create Category</button>
    </form>
@endsection
