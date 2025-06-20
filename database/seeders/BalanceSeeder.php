<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            \App\Models\Balance::create([
                'user_id' => $i,
                'current_balance' => 1000000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
