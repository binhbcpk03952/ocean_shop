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
        Schema::create('promotion_categories', function (Blueprint $table) {
            $table->id('promotion_category_id');
            $table->integer('promotion_id')
                  ->constrained(table: 'promotions', column: 'promotion_id')
                  ->onDelete('cascade');
            $table->integer('category_id')
                  ->constrained(table: 'categories', column: 'category_id')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_categories');
    }
};