<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige registros órfãos (company_id NULL) em `batches` e `transfers`.
 *
 * Necessário após aplicar o global scope `HasCompanyScope` a esses models:
 * linhas com company_id NULL ficariam invisíveis para usuários não-master.
 * O company_id é derivado deterministicamente do tanque de origem.
 *
 * Idempotente: só toca linhas com company_id NULL. Subquery correlacionada
 * portável (MySQL + SQLite).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('batches')
            ->whereNull('company_id')
            ->update([
                'company_id' => DB::raw('(SELECT company_id FROM tanks WHERE tanks.id = batches.tank_id)'),
            ]);

        DB::table('transfers')
            ->whereNull('company_id')
            ->update([
                'company_id' => DB::raw('(SELECT company_id FROM tanks WHERE tanks.id = transfers.origin_tank_id)'),
            ]);
    }

    public function down(): void
    {
        // Backfill corretivo — não é revertido (não há como distinguir os NULLs originais).
    }
};
