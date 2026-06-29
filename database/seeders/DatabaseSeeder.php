<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin account
        User::create([
            'name' => 'Administrator',
            'nip' => 'ADMIN001',
            'tanggal_lahir' => '1990-01-01',
            'email' => 'admin123@gmail.com',
            'password' => Hash::make('Admin123'),
            'role' => 'admin',
        ]);
    }
}