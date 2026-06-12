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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('brand_id')->constrained();
            $table->string('order_code', 50)->unique();
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->text('customer_address');
            $table->enum('courier', ['jne', 'jnt', 'sicepat']);
            $table->enum('payment_method', ['bank_transfer', 'qris']);
            $table->string('payment_proof_path');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('total_price');
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'done', 'cancelled'])
                  ->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
