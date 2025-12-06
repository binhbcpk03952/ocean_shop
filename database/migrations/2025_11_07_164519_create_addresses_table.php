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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id('address_id');
            $table->foreignId('user_id')
                  ->constrained(table: 'users', column: 'user_id')
                  ->onDelete('cascade');
            $table->string('recipient_name');
            $table->integer('recipient_phone');
            $table->string('street_address');
            $table->string('ward');
            $table->string('district');
            $table->string('province');
            $table->string('type');
            $table->boolean('is_default');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};