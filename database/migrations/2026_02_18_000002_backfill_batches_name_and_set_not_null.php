<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration
{
    public function up(): void
    {
        // MySQL: backfill para bases existentes (não pode ficar null/vazio).
        DB::statement("
            UPDATE `batches`
               SET `name` = CONCAT('Lote ', `id`)
             WHERE `name` IS NULL OR `name` = ''
        ");

        DB::statement("ALTER TABLE `batches` MODIFY `name` VARCHAR(255) NOT NULL");
    }

    public function down(): void
    {
        // MySQL: reverte apenas a constraint (não desfaz o backfill de dados).
        DB::statement("ALTER TABLE `batches` MODIFY `name` VARCHAR(255) NULL");
    }
};
