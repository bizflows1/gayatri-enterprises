<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Folder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Specific Folder Names to Rename
        $renames = [
            '2026-27 (Upcoming)' => '2026-2027',
            '2025-26 (Upcoming)' => '2025-2026',
            '2024-25 (Current)'  => '2024-2025',
            '2023-24'            => '2023-2024',
        ];

        foreach ($renames as $oldName => $newName) {
            Folder::where('name', $oldName)->update([
                'name' => $newName,
                'path' => $newName // Since path is usually just the name for root folders
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed really, but good practice
    }
};
