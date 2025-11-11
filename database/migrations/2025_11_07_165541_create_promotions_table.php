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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id('promotion_id');
            $table->string('code');
            $table->string('description');
            $table->string('discount_type');
            $table->integer('discount_value');
            $table->integer('min_order_amount');
            $table->integer('max_discount_amount');
            $table->integer('start_date');
            $table->integer('end_date');
            $table->integer('used_count');
            $table->boolean('is_active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
