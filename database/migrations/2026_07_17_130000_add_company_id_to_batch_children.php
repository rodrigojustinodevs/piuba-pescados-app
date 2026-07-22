<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Wave B do isolamento multi-tenant.
 *
 * Adiciona `company_id` às entidades operacionais que só tinham vínculo com a
 * empresa via `batch_id`, e faz backfill a partir de `batches.company_id`
 * (já populado pela migration anterior). Habilita `HasCompanyScope` nesses
 * models: Biometry, Mortality, Feeding, Harvest, GrowthCurve, Stocking.
 *
 * Derivação 100% coberta (todo registro possui batch_id não-nulo).
 */
return new class () extends Migration
{
    /** @var list<string> */
    private array $tables = [
        'biometries',
        'mortalities',
        'feedings',
        'harvests',
        'growth_curves',
        'stockings',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $t): void {
                    $t->char('company_id', 36)->nullable();
                    $t->index('company_id');
                });
            }
        }

        // Backfill do company_id a partir do batch (subquery correlacionada portável).
        foreach ($this->tables as $table) {
            DB::table($table)
                ->whereNull('company_id')
                ->update([
                    'company_id' => DB::raw("(SELECT company_id FROM batches WHERE batches.id = {$table}.batch_id)"),
                ]);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $t): void {
                    $t->dropColumn('company_id');
                });
            }
        }
    }
};
