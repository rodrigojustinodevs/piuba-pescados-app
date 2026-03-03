<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    private const array TABLES_WITH_BATCHE_ID = [
        'biometries',
        'feedings',
        'growth_curves',
        'harvests',
        'mortalities',
        'sales',
        'stockings',
        'transfers',
    ];

    /**
     * Resolve the actual foreign key constraint name from the database.
     * Needed because renamed tables (e.g. stockings from settlements) keep the original FK name in MySQL.
     */
    private function getBatcheIdForeignKeyName(string $table): ?string
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $name = DB::selectOne(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = 'batche_id' AND REFERENCED_TABLE_NAME = 'batches'
                 LIMIT 1",
                [DB::getDatabaseName(), $table]
            );

            return $name?->CONSTRAINT_NAME ?? "{$table}_batche_id_foreign";
        }

        return "{$table}_batche_id_foreign";
    }

    private function getBatchIdForeignKeyName(string $table): ?string
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $name = DB::selectOne(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = 'batch_id' AND REFERENCED_TABLE_NAME = 'batches'
                 LIMIT 1",
                [DB::getDatabaseName(), $table]
            );

            return $name?->CONSTRAINT_NAME ?? "{$table}_batch_id_foreign";
        }

        return "{$table}_batch_id_foreign";
    }

    /**
     * Run the migrations.
     * Renames batche_id to batch_id and updates foreign keys (correct aquaculture terminology).
     */
    public function up(): void
    {
        Schema::getConnection()->getDriverName();

        foreach (self::TABLES_WITH_BATCHE_ID as $table) {
            if (! Schema::hasColumn($table, 'batche_id')) {
                continue;
            }

            $fkName = $this->getBatcheIdForeignKeyName($table);

            Schema::table($table, function ($blueprint) use ($fkName): void {
                $blueprint->dropForeign($fkName);
            });
            DB::statement("ALTER TABLE `{$table}` CHANGE batche_id batch_id CHAR(36) NOT NULL");
            Schema::table($table, function ($blueprint): void {
                $blueprint->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::getConnection()->getDriverName();

        foreach (self::TABLES_WITH_BATCHE_ID as $table) {
            if (! Schema::hasColumn($table, 'batch_id')) {
                continue;
            }

            $fkName = $this->getBatchIdForeignKeyName($table);

            Schema::table($table, function ($blueprint) use ($fkName): void {
                $blueprint->dropForeign($fkName);
            });
            DB::statement("ALTER TABLE `{$table}` CHANGE batch_id batche_id CHAR(36) NOT NULL");
            Schema::table($table, function ($blueprint): void {
                $blueprint->foreign('batche_id')->references('id')->on('batches')->onDelete('cascade');
            });
        }
    }
};
