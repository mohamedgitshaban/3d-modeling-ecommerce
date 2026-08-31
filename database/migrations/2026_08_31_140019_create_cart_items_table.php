<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            // polymorphic: ProductVariant or Collection
            $table->string('itemable_type');
            $table->unsignedBigInteger('itemable_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->json('collection_selection')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();

            $table->index(['itemable_type', 'itemable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
