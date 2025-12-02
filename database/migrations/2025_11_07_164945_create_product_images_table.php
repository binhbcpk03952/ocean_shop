<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id('image_id');
            $table->foreignId('product_id')->index()
                  ->constrained(table: 'products', column: 'product_id')
                  ->onDelete('cascade');
            $table->foreignId('variant_id')->index()
                  ->constrained(table: 'product_variants', column: 'variant_id')
                  ->onDelete('cascade');
            $table->string('image_url');
            $table->boolean('is_main');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};