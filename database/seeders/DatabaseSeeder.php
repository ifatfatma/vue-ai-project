<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin User (Aapke admin panel ke liye)
         User::updateOrCreate(
            ['email' => 'test@gmail.com'],
            [
                'name' => 'Admin',
                'user_type' => 'admin',
                'password' => Hash::make('12345678'),
            ]
        );  

        // 2. Frontend OTP User (Aapke real email testing ke liye)
        User::updateOrCreate(
            ['email' => 'fatmaifat663@gmail.com'],
            [
                'name' => 'Fatma',
                'password' => Hash::make('12345678'),
            ]
        );

        // 3. CategorySeeder Call Karein
        $this->call([
            CategorySeeder::class,
        ]);
    }
}