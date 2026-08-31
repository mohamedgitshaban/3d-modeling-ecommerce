<?php

namespace App\Providers;

use App\Models\ProductCollection;
use App\Models\ProductVariant;
use App\Services\Payments\PaymentGatewayContract;
use App\Services\Payments\PaymobGateway;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayContract::class, PaymobGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Stable morph aliases for the polymorphic cart_items/order_items "itemable" relation,
        // so stored type strings never break if a model gets renamed/namespaced later.
        Relation::morphMap([
            'product_variant' => ProductVariant::class,
            'collection' => ProductCollection::class,
        ]);
    }
}
