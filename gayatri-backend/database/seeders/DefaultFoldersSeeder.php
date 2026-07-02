<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Folder;

class DefaultFoldersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultFolders = [
            '2025-26 (Upcoming)',
            '2024-25 (Current)',
            '2023-24',
            'KYC Documents'
        ];

        // Get all clients
        $clients = User::where('role', 'client')->get();

        foreach ($clients as $client) {
            foreach ($defaultFolders as $folderName) {
                // Check if folder already exists
                $exists = Folder::where('user_id', $client->id)
                    ->where('name', $folderName)
                    ->where('parent_id', null)
                    ->exists();

                if (!$exists) {
                    Folder::create([
                        'user_id' => $client->id,
                        'name' => $folderName,
                        'parent_id' => null,
                        'path' => $folderName
                    ]);
                }
            }
        }

        $this->command->info('Default folders created for all clients!');
    }
}
