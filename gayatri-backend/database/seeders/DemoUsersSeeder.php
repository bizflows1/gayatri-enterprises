<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * One user per role for local testing — admin/staff for the ops side,
 * one client (with its business-profile row) for the portal/ordering flow.
 */
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@gayatrient.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $staff = User::firstOrCreate(
            ['email' => 'staff@gayatrient.com'],
            [
                'name' => 'Staff User',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'is_active' => true,
            ]
        );

        $clientUser = User::firstOrCreate(
            ['email' => 'client@gayatrient.com'],
            [
                'name' => 'Client User',
                'password' => Hash::make('password123'),
                'role' => 'client',
                'is_active' => true,
            ]
        );

        Client::firstOrCreate(
            ['user_id' => $clientUser->id],
            [
                'company_name' => 'Apex Diagnostics Pvt Ltd',
                'gstin' => '27AAACA1234F1Z5',
                'credit_limit' => 50000,
                'outstanding_balance' => 0,
                'status' => 'active',
            ]
        );

        $this->command->info('Admin:  admin@gayatrient.com  / password123');
        $this->command->info('Staff:  staff@gayatrient.com  / password123');
        $this->command->info('Client: client@gayatrient.com / password123');
    }
}
