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
        Schema::create('bank_transfers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('bank_code');        // kode bank
        $table->string('bank_name');        // nama bank
        $table->string('account_number');   // nomor tujuan
        $table->string('account_name');     // nama tujuan
        $table->integer('amount');          // nominal transfer
        $table->string('status')->default('pending'); // pending/success/failed
        $table->string('transaction_id')->unique();   // ID unik
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transfers');
    }
};
