@extends('admin.layouts.app')

@section('title', 'Edit '.$category->name)
@section('heading', 'Edit Category: '.$category->name)

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="bg-white border border-stone-200 rounded-lg p-6 space-y-4 h-fit">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium mb-1">Parent Category</label>
                <select name="parent_id" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                    <option value="">None (top-level)</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected($category->parent_id === $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" required value="{{ $category->name }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">{{ $category->description }}</textarea>
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($category->is_active)> Active</label>
            <button class="bg-stone-900 text-white text-sm font-medium px-4 py-2 rounded-md">Save</button>
        </form>

        <div class="space-y-6">
            <div class="bg-white border border-stone-200 rounded-lg p-6">
                <h2 class="font-semibold mb-1">Product Detail Schema</h2>
                <p class="text-xs text-stone-500 mb-4">Every product in this category will show these sections on its page — exactly like Overview / More Features / Certifications / Specifications / Info &amp; Guides. Add a "Handle Finish" or "Size" field and check "Drives Variants" to make it generate separate SKUs/prices.</p>

                <form method="POST" action="{{ route('admin.categories.attribute-groups.store', $category) }}" class="flex gap-2 mb-6">
                    @csrf
                    <input type="text" name="key" placeholder="key (e.g. specifications)" required class="border border-stone-300 rounded-md py-1.5 px-2 text-xs w-40">
                    <input type="text" name="label" placeholder="Label (e.g. Specifications)" required class="border border-stone-300 rounded-md py-1.5 px-2 text-xs flex-1">
                    <select name="type" class="border border-stone-300 rounded-md py-1.5 px-2 text-xs">
                        <option value="richtext">Rich text / bullets</option>
                        <option value="key_value">Key/Value grid</option>
                        <option value="badge_list">Badge list</option>
                        <option value="file_list">File list</option>
                    </select>
                    <button class="bg-stone-900 text-white text-xs font-medium px-3 rounded-md">Add Section</button>
                </form>

                @foreach ($category->attributeGroups as $group)
                    <div class="border border-stone-200 rounded-md p-4 mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="font-medium text-sm">{{ $group->label }} <span class="text-stone-400 font-normal">({{ $group->type }})</span></div>
                            <form method="POST" action="{{ route('admin.categories.attribute-groups.destroy', [$category, $group]) }}">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-600 hover:underline">Remove section</button>
                            </form>
                        </div>

                        <ul class="text-sm space-y-1 mb-3">
                            @foreach ($group->fields as $attribute)
                                <li class="flex items-center justify-between border-b border-stone-50 pb-1">
                                    <span>{{ $attribute->label }} <span class="text-xs text-stone-400">({{ $attribute->input_type }}@if($attribute->is_variant_option), variant option @endif)</span></span>
                                    <form method="POST" action="{{ route('admin.attribute-groups.attributes.destroy', [$group, $attribute]) }}">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-600 hover:underline">Remove</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>

                        <form method="POST" action="{{ route('admin.attribute-groups.attributes.store', $group) }}" class="flex flex-wrap gap-2 items-center">
                            @csrf
                            <input type="text" name="key" placeholder="key" required class="border border-stone-300 rounded-md py-1 px-2 text-xs w-24">
                            <input type="text" name="label" placeholder="Label" required class="border border-stone-300 rounded-md py-1 px-2 text-xs w-32">
                            <select name="input_type" class="border border-stone-300 rounded-md py-1 px-2 text-xs">
                                <option value="text">Text</option>
                                <option value="textarea">Textarea</option>
                                <option value="select">Select</option>
                                <option value="file">File</option>
                            </select>
                            <input type="text" name="options" placeholder="Options (comma sep, for Select)" class="border border-stone-300 rounded-md py-1 px-2 text-xs w-48">
                            <label class="text-xs flex items-center gap-1"><input type="checkbox" name="is_variant_option" value="1"> Drives Variants</label>
                            <label class="text-xs flex items-center gap-1"><input type="checkbox" name="is_filterable" value="1"> Filterable</label>
                            <button class="bg-stone-100 text-stone-800 text-xs font-medium px-3 py-1 rounded-md">Add Field</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
