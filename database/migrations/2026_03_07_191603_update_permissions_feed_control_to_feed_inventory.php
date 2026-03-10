<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration
{
    private const array PERMISSION_MAP = [
        'create-feed-control' => 'create-feed-inventory',
        'view-feed-control'   => 'view-feed-inventory',
        'update-feed-control' => 'update-feed-inventory',
        'delete-feed-control' => 'delete-feed-inventory',
    ];

    /**
     * Run the migrations.
     * Atualiza nomes das permissões de feed-control para feed-inventory.
     */
    public function up(): void
    {
        foreach (self::PERMISSION_MAP as $oldName => $newName) {
            DB::table('permissions')
                ->where('name', $oldName)
                ->update(['name' => $newName]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::PERMISSION_MAP as $oldName => $newName) {
            DB::table('permissions')
                ->where('name', $newName)
                ->update(['name' => $oldName]);
        }
    }
};
