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
        collect([
            'Daffa Hanif Rabbanee',
            'Adzra Pravitadewa Firjatulah',
            'William Marcello',
            'Michael Ciang',
            'Xaverius William Alvin',
        ])->each(function ($name) {
            $email = $name === 'Daffa Hanif Rabbanee'
                ? 'daffarabbanee@gmail.com'
                : strtolower(str_replace(' ', '', $name)) . '@gmail.com';

            \App\Models\User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('Testing321'),
                'account_number' => (string) rand(1, 9) . substr(str_shuffle("0123456789"), 0, 9),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}
