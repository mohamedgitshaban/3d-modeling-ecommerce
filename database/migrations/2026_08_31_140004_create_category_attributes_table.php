<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_attribute_group_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('label');
            $table->enum('input_type', ['text', 'textarea', 'select', 'number', 'boolean', 'file'])->default('text');
            $table->json('options')->nullable();
            $table->boolean('is_variant_option')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['category_attribute_group_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_attributes');
    }
};
