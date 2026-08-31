<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryAttributeController;
use App\Http\Controllers\Admin\CategoryAttributeGroupController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\Model3DController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\StoreLocationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Catalog
    Route::resource('categories', CategoryController::class)->except('show');
    Route::post('categories/{category}/attribute-groups', [CategoryAttributeGroupController::class, 'store'])->name('categories.attribute-groups.store');
    Route::delete('categories/{category}/attribute-groups/{attributeGroup}', [CategoryAttributeGroupController::class, 'destroy'])->name('categories.attribute-groups.destroy');
    Route::post('attribute-groups/{attributeGroup}/attributes', [CategoryAttributeController::class, 'store'])->name('attribute-groups.attributes.store');
    Route::delete('attribute-groups/{attributeGroup}/attributes/{attribute}', [CategoryAttributeController::class, 'destroy'])->name('attribute-groups.attributes.destroy');

    Route::resource('brands', BrandController::class)->except(['show', 'create', 'edit']);

    Route::resource('products', ProductController::class)->except('show');
    Route::post('products/{product}/attributes', [ProductController::class, 'updateAttributes'])->name('products.attributes.update');
    Route::post('products/{product}/variants/generate', [ProductVariantController::class, 'generate'])->name('products.variants.generate');
    Route::put('variants/{variant}', [ProductVariantController::class, 'update'])->name('variants.update');
    Route::delete('variants/{variant}', [ProductVariantController::class, 'destroy'])->name('variants.destroy');

    // Media: 3D models + gallery, per product or per variant
    Route::post('products/{product}/model-3d', [Model3DController::class, 'storeForProduct'])->name('products.model3d.store');
    Route::post('variants/{variant}/model-3d', [Model3DController::class, 'storeForVariant'])->name('variants.model3d.store');
    Route::delete('model-3d/{mediaId}', [Model3DController::class, 'destroy'])->name('model3d.destroy');
    Route::post('products/{product}/gallery', [GalleryController::class, 'storeForProduct'])->name('products.gallery.store');
    Route::post('variants/{variant}/gallery', [GalleryController::class, 'storeForVariant'])->name('variants.gallery.store');
    Route::delete('gallery/{mediaId}', [GalleryController::class, 'destroy'])->name('gallery.destroy');

    // Inventory
    Route::get('stock', [StockController::class, 'index'])->name('stock.index');
    Route::post('variants/{variant}/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
    Route::put('stock-items/{stockItem}', [StockController::class, 'updateThreshold'])->name('stock.threshold.update');

    // Marketing
    Route::resource('coupons', CouponController::class)->except('show');
    Route::resource('offers', OfferController::class)->only(['index', 'create', 'store', 'update', 'destroy']);
    Route::resource('collections', CollectionController::class)->except('show');
    Route::post('collections/{collection}/slots', [CollectionController::class, 'storeSlot'])->name('collections.slots.store');
    Route::delete('collections/{collection}/slots/{slot}', [CollectionController::class, 'destroySlot'])->name('collections.slots.destroy');

    // Sales
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status.update');
    Route::post('orders/{order}/ship', [OrderController::class, 'markShipped'])->name('orders.ship');

    // Settings
    Route::get('shipping', [ShippingController::class, 'index'])->name('shipping.index');
    Route::post('shipping/zones', [ShippingController::class, 'storeZone'])->name('shipping.zones.store');
    Route::delete('shipping/zones/{zone}', [ShippingController::class, 'destroyZone'])->name('shipping.zones.destroy');
    Route::post('shipping/zones/{zone}/rates', [ShippingController::class, 'storeRate'])->name('shipping.rates.store');
    Route::delete('shipping/zones/{zone}/rates/{rate}', [ShippingController::class, 'destroyRate'])->name('shipping.rates.destroy');

    Route::resource('store-locations', StoreLocationController::class)->except(['show', 'create', 'edit']);
});
