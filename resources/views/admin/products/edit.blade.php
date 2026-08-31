@extends('admin.layouts.app')

@section('title', 'Edit '.$product->name)
@section('heading', 'Edit Product')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" class="bg-white border border-stone-200 rounded-lg p-6 space-y-4 h-fit">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Category</label>
                    <select name="category_id" required class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($product->category_id === $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Brand</label>
                    <select name="brand_id" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                        <option value="">None</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @selected($product->brand_id === $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" required value="{{ $product->name }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Base SKU</label>
                    <input type="text" name="base_sku" required value="{{ $product->base_sku }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">MSRP</label>
                    <input type="number" step="0.01" name="msrp" value="{{ $product->msrp }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Collection Line</label>
                <input type="text" name="collection_line" value="{{ $product->collection_line }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Short Description</label>
                <textarea name="short_description" rows="3" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">{{ $product->short_description }}</textarea>
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($product->is_active)> Active</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1" @checked($product->is_featured)> Featured on homepage</label>
            <button class="bg-stone-900 text-white text-sm font-medium px-4 py-2 rounded-md">Save</button>
        </form>

        <div class="bg-white border border-stone-200 rounded-lg p-6 h-fit">
            <h2 class="font-semibold mb-1">3D Model</h2>
            <p class="text-xs text-stone-500 mb-4">Upload a .glb (web viewer) and optional .usdz (iOS AR). This is the fallback shown when a variant has no model of its own.</p>
            <form method="POST" action="{{ route('admin.products.model3d.store', $product) }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="file" name="model_glb" accept=".glb,.gltf" class="text-sm">
                <input type="file" name="model_usdz" accept=".usdz" class="text-sm">
                <input type="file" name="poster" accept="image/*" class="text-sm">
                <button class="bg-stone-900 text-white text-sm font-medium px-4 py-2 rounded-md">Upload 3D Model</button>
            </form>

            @if ($product->getMedia('models_3d')->isNotEmpty())
                <ul class="mt-4 text-sm space-y-1">
                    @foreach ($product->getMedia('models_3d') as $media)
                        <li class="flex justify-between items-center">
                            <span>{{ $media->file_name }}</span>
                            <form method="POST" action="{{ route('admin.model3d.destroy', $media->id) }}">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-600 hover:underline">Remove</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif

            <h2 class="font-semibold mt-6 mb-1">Gallery</h2>
            <form method="POST" action="{{ route('admin.products.gallery.store', $product) }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="file" name="images[]" accept="image/*" multiple class="text-sm">
                <button class="bg-stone-900 text-white text-sm font-medium px-4 py-2 rounded-md">Upload Images</button>
            </form>
            <div class="mt-4 grid grid-cols-4 gap-2">
                @foreach ($product->getMedia('gallery') as $media)
                    <div class="relative">
                        <img src="{{ $media->getUrl() }}" class="aspect-square object-cover rounded border border-stone-200">
                        <form method="POST" action="{{ route('admin.gallery.destroy', $media->id) }}" class="absolute top-1 right-1">
                            @csrf @method('DELETE')
                            <button class="bg-white/80 text-red-600 text-xs px-1 rounded">✕</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-white border border-stone-200 rounded-lg p-6 mb-8">
        <h2 class="font-semibold mb-1">Product Detail Specifications</h2>
        <p class="text-xs text-stone-500 mb-4">Fields come from the "{{ $product->category->name }}" category schema — manage the schema itself from the category's edit page.</p>

        <form method="POST" action="{{ route('admin.products.attributes.update', $product) }}" class="space-y-6">
            @csrf
            @foreach ($product->category->attributeGroups as $group)
                @php $existing = $product->attributeValues->keyBy('category_attribute_id'); @endphp
                <div>
                    <h3 class="text-sm font-semibold text-stone-700 mb-2">{{ $group->label }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($group->fields as $attribute)
                            <div>
                                <label class="block text-xs text-stone-500 mb-1">{{ $attribute->label }}</label>
                                @if ($attribute->input_type === 'textarea')
                                    <textarea name="values[{{ $attribute->id }}]" rows="3" class="w-full border border-stone-300 rounded-md py-1.5 px-2 text-sm">{{ $existing[$attribute->id]->value ?? '' }}</textarea>
                                @elseif ($attribute->input_type === 'select')
                                    <select name="values[{{ $attribute->id }}]" class="w-full border border-stone-300 rounded-md py-1.5 px-2 text-sm">
                                        <option value="">—</option>
                                        @foreach (($attribute->options ?? []) as $option)
                                            <option value="{{ $option }}" @selected(($existing[$attribute->id]->value ?? null) === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" name="values[{{ $attribute->id }}]" value="{{ $existing[$attribute->id]->value ?? '' }}" class="w-full border border-stone-300 rounded-md py-1.5 px-2 text-sm">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
            <button class="bg-stone-900 text-white text-sm font-medium px-4 py-2 rounded-md">Save Specifications</button>
        </form>
    </div>

    <div class="bg-white border border-stone-200 rounded-lg p-6">
        <h2 class="font-semibold mb-1">Variants</h2>
        <p class="text-xs text-stone-500 mb-4">Generate every combination of this category's variant options (e.g. Handle Finish × Vanity Finish × Size), then set price/SKU/stock per combination.</p>

        <form method="POST" action="{{ route('admin.products.variants.generate', $product) }}" class="space-y-2 mb-6">
            @csrf
            @foreach ($product->category->variantOptionAttributes() as $attribute)
                <div class="flex items-center gap-2">
                    <label class="text-sm w-40">{{ $attribute->label }}</label>
                    <input type="text" name="options[{{ $attribute->id }}]" placeholder="comma,separated,values" class="flex-1 border border-stone-300 rounded-md py-1.5 px-2 text-sm">
                </div>
            @endforeach
            <button class="bg-stone-100 text-stone-800 text-sm font-medium px-4 py-2 rounded-md">Generate Variant Combinations</button>
        </form>

        <table class="w-full text-sm">
            <thead class="text-left text-stone-500 border-b border-stone-100">
                <tr><th class="p-2">Options</th><th class="p-2">SKU</th><th class="p-2">Price</th><th class="p-2">Default</th><th class="p-2">Active</th><th class="p-2">Stock</th><th class="p-2"></th></tr>
            </thead>
            <tbody>
                @foreach ($product->variants as $variant)
                    <tr class="border-b border-stone-50 align-top">
                        <form method="POST" action="{{ route('admin.variants.update', $variant) }}">
                            @csrf @method('PUT')
                            <td class="p-2 text-xs text-stone-500">{{ implode(' / ', $variant->optionLabels()) ?: '—' }}</td>
                            <td class="p-2"><input type="text" name="sku" value="{{ $variant->sku }}" class="w-32 border border-stone-300 rounded-md py-1 px-2 text-xs"></td>
                            <td class="p-2"><input type="number" step="0.01" name="price" value="{{ $variant->price }}" class="w-24 border border-stone-300 rounded-md py-1 px-2 text-xs"></td>
                            <td class="p-2 text-center"><input type="radio" name="is_default" value="1" @checked($variant->is_default)></td>
                            <td class="p-2 text-center"><input type="checkbox" name="is_active" value="1" @checked($variant->is_active)></td>
                            <td class="p-2 text-xs">
                                @foreach ($variant->stockItems as $stockItem)
                                    {{ $stockItem->salesChannel->name }}: {{ $stockItem->quantity_on_hand }}<br>
                                @endforeach
                            </td>
                            <td class="p-2"><button class="text-amber-700 hover:underline text-xs">Save</button></td>
                        </form>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($product->variants->isNotEmpty())
            <div class="mt-6 border-t border-stone-200 pt-4">
                <h3 class="text-sm font-semibold mb-2">Adjust Stock</h3>
                <form method="POST" action="{{ route('admin.stock.adjust', $product->variants->first()) }}" class="flex items-center gap-2">
                    @csrf
                    <select name="variant_override" onchange="this.form.action = this.form.action.replace(/variants\/\d+/, 'variants/' + this.value)" class="border border-stone-300 rounded-md py-1.5 px-2 text-xs">
                        @foreach ($product->variants as $variant)
                            <option value="{{ $variant->id }}">{{ $variant->sku }}</option>
                        @endforeach
                    </select>
                    <select name="sales_channel_id" class="border border-stone-300 rounded-md py-1.5 px-2 text-xs">
                        @foreach (\App\Models\SalesChannel::all() as $channel)
                            <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="delta" placeholder="+10 or -5" required class="w-24 border border-stone-300 rounded-md py-1.5 px-2 text-xs">
                    <button class="bg-stone-900 text-white text-xs font-medium px-3 py-1.5 rounded-md">Adjust</button>
                </form>
                <p class="text-xs text-stone-400 mt-1">Full stock management with search/filter is on the <a href="{{ route('admin.stock.index') }}" class="underline">Stock</a> page.</p>
            </div>
        @endif
    </div>
@endsection
