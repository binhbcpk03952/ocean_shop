<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // ...
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id('post_id');
            $table->foreignId('user_id')->constrained(table: 'users', column: 'user_id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content'); // Dùng longText cho nội dung Quill
            $table->string('thumbnail_path')->nullable(); // Đường dẫn đến ảnh
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
