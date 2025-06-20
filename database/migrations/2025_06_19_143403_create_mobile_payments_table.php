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
        Schema::create('mobile_payments', function (Blueprint $table) {
             $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('payment_type');
        $table->string('transaction_id')->nullable();
        $table->string('customer_number')->nullable();
        $table->decimal('amount', 12, 2);
        $table->string('status')->default('pending');
        $table->json('metadata')->nullable(); // untuk metadata pdam
        $table->string('pln_token_1')->nullable(); // untuk PLN
        $table->string('kwh_amount')->nullable();  // untuk PLN
        $table->text('error_message')->nullable(); // untuk PLN gagal
        $table->timestamp('paid_at')->nullable();  // waktu bayar
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_payments');
    }
};
