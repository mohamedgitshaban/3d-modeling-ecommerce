<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Admin 3D Model Manager: upload a .glb/.gltf (web viewer) and optional .usdz
 * (iOS Quick Look AR) per product, or override per variant when a finish has
 * its own textured model. A poster/thumbnail image can be uploaded alongside
 * it for card previews and Open Graph images.
 */
class Model3DController extends Controller
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
        $request->validate([
            'model_glb' => ['nullable', 'file', 'max:51200', 'mimes:glb,gltf'],
            'model_usdz' => ['nullable', 'file', 'max:51200'],
            'poster' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('model_glb')) {
            $model->addMedia($request->file('model_glb'))->toMediaCollection('models_3d');
        }

        if ($request->hasFile('model_usdz')) {
            $model->addMedia($request->file('model_usdz'))->toMediaCollection('models_3d');
        }

        if ($request->hasFile('poster')) {
            $model->addMedia($request->file('poster'))->toMediaCollection('model_posters');
        }

        Log::info('3D model asset uploaded', ['model' => get_class($model), 'id' => $model->getKey()]);

        return back()->with('status', '3D model uploaded.');
    }

    public function destroy(Request $request, int $mediaId): RedirectResponse
    {
        $media = Media::findOrFail($mediaId);
        $media->delete();

        return back()->with('status', '3D model asset removed.');
    }
}
