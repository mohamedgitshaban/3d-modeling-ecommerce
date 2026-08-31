<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variant_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->timestamps();

            $table->unique(['product_variant_id', 'category_attribute_id'], 'variant_option_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_option_values');
    }
};
