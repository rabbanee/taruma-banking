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
          Schema::create('pdam_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // relasi ke users table
            $table->string('customer_number'); // nomor pelanggan PDAM
            $table->string('customer_name')->nullable();
            $table->string('city'); // kota PDAM
            $table->decimal('amount', 12, 2); // total tagihan atau nominal
            $table->string('status')->default('pending'); // status transaksi
            $table->string('reference')->nullable(); // kode referensi
            $table->timestamp('paid_at')->nullable(); // waktu pembayaran
            $table->timestamps(); // created_at & updated_at

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdam_transactions');
    }
};
