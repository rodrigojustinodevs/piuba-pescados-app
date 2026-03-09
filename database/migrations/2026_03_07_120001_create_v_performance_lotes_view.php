<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE VIEW v_performance_lotes AS
            SELECT
                b.id AS batch_id,
                b.name AS batch_name,
                bio.biometry_date,
                bio.average_weight,
                (bio.average_weight - LAG(bio.average_weight) OVER (PARTITION BY bio.batch_id ORDER BY bio.biometry_date)) AS weight_gain,
                DATEDIFF(bio.biometry_date, LAG(bio.biometry_date) OVER (PARTITION BY bio.batch_id ORDER BY bio.biometry_date)) AS days_between,
                bio.fcr
            FROM biometries bio
            JOIN batches b ON bio.batch_id = b.id
            SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_performance_lotes');
    }
};
