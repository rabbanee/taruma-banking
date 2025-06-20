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
            // Identitas user
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Informasi transaksi
            $table->string('transaction_id')->unique()->nullable(); // ID transaksi global
            $table->string('payment_type');                          // Jenis: PLN, Pulsa, dll
            $table->string('customer_number')->nullable();           // No pelanggan (umum)
            $table->string('token_number', 12)->nullable();          // Token PLN (khusus)
            $table->decimal('amount', 12, 2);                        // Nominal pembayaran

            // Detail PLN (opsional)
            $table->string('pln_token_1', 20)->nullable();           // Token 1
            $table->decimal('kwh_amount', 8, 2)->nullable();         // KWh yang dibeli

            // Status & kontrol
            $table->string('status')->default('pending');           // Status pembayaran
            $table->timestamp('paid_at')->nullable();               // Kapan dibayar
            $table->text('error_message')->nullable();              // Error jika gagal

            // Metadata & admin
            $table->json('metadata')->nullable();                   // Info tambahan
            $table->timestamps();                                   // created_at & updated_at

            // Index untuk efisiensi query kombinasi
            $table->index(['user_id', 'payment_type']);
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
