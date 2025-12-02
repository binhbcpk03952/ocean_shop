<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id('review_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');

            $table->tinyInteger('rating'); // 1–5
            $table->text('content')->nullable();

            $table->enum('status', ['approved', 'hidden', 'deleted'])
                  ->default('approved');

            $table->timestamps();

            // 1 user chỉ được review 1 lần / sản phẩm
            $table->unique(['user_id', 'product_id']);

            // foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
