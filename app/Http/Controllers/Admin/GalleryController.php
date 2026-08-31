<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class GalleryController extends Controller
{
    public function storeForProduct(Request $request, Product $product): RedirectResponse
    {
        return $this->store($request, $product);
    }

    public function storeForVariant(Request $request, ProductVariant $variant): RedirectResponse
    {
        return $this->store($request, $variant);
    }

    protected function store(Request $request, HasMedia $model): RedirectResponse
    {
        $request->validate(['images' => ['required', 'array'], 'images.*' => ['image', 'max:10240']]);

        foreach ($request->file('images') as $file) {
            $model->addMedia($file)->toMediaCollection('gallery');
        }

        return back()->with('status', 'Images uploaded.');
    }

    public function destroy(int $mediaId): RedirectResponse
    {
        Media::findOrFail($mediaId)->delete();

        return back()->with('status', 'Image removed.');
    }
}
