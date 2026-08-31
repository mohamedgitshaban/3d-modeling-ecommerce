<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Storefront\Account\OrderController as AccountOrderController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CategoryController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\CollectionController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\OrderTrackingController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\StoreLocatorController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/category/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/collections/{collection:slug}', [CollectionController::class, 'show'])->name('collections.show');
Route::get('/where-to-buy', [StoreLocatorController::class, 'index'])->name('store-locator.index');

// Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/variants', [CartController::class, 'storeVariant'])->name('cart.variants.store');
Route::post('/cart/collections', [CartController::class, 'storeCollection'])->name('cart.collections.store');
Route::patch('/cart/items/{cartItem}', [CartController::class, 'updateQuantity'])->name('cart.items.update');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

// Checkout
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/thank-you/{order}', [CheckoutController::class, 'thankYou'])->name('checkout.thank-you');

// Order tracking (post-payment) — no login required, order number + email
Route::get('/track-order', [OrderTrackingController::class, 'create'])->name('orders.track.create');
Route::post('/track-order', [OrderTrackingController::class, 'lookup'])->name('orders.track.lookup');
Route::get('/orders/track/{order}', [OrderTrackingController::class, 'show'])->name('orders.track.show');
Route::get('/orders/track/{order}/status', [OrderTrackingController::class, 'status'])->name('orders.track.status');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Customer account
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/orders', [AccountOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AccountOrderController::class, 'show'])->name('orders.show');
});

require __DIR__.'/admin.php';
require __DIR__.'/webhooks.php';
