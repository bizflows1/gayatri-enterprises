<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin',
            'phone' => '9696969696',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
           
        ]);

        // Create a test client
        User::create([
            'name' => 'Test Client',
            'phone' => '9999999999',
            'email' => 'client@example.com',
            'password' => Hash::make('client123'),
            'role' => 'client',
            'is_active' => true,
        ]);

        $this->command->info('Admin and test users created successfully!');
    }
}
