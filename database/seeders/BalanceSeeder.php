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
        \App\Models\Balance::create(
            [
                'user_id' => 1,
                'current_balance' => 1000000,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
