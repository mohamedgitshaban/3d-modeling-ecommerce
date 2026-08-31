<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['percentage_off', 'fixed_off', 'bundle_discount']);
            $table->decimal('value', 10, 2);
            $table->enum('target_type', ['product', 'category', 'collection']);
            $table->unsignedBigInteger('target_id');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('badge_label')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
