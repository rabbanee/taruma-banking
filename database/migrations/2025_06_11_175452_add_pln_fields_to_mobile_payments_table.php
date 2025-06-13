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
        Schema::table('mobile_payments', function (Blueprint $table) {
            // Tambah kolom untuk PLN
            $table->string('transaction_id')->unique()->nullable()->after('id');
            $table->string('token_number', 12)->nullable()->after('payment_type');
            $table->string('pln_token_1', 20)->nullable()->after('amount');
            $table->string('pln_token_2', 20)->nullable()->after('pln_token_1');
            $table->decimal('kwh_amount', 8, 2)->nullable()->after('pln_token_2');
            $table->text('error_message')->nullable()->after('status');
            
            // Index untuk performance
            $table->index(['user_id', 'payment_type']);
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('mobile_payments', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'payment_type']);
            $table->dropIndex(['transaction_id']);
            
            $table->dropColumn([
                'transaction_id',
                'token_number',
                'pln_token_1', 
                'kwh_amount',
                'error_message'
            ]);
        });
    }
    };