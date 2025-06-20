<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Daffa Hanif Rabbanee',
            'email' => 'daffarabbanee@gmail.com',
            'password' => Hash::make('Daffa321'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
