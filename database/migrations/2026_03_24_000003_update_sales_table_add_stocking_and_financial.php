<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        $hadBatcheColumn = Schema::hasColumn('sales', 'batche_id');

        Schema::table('sales', function (Blueprint $table): void {
            // Fix original typo: batche_id → batch_id
            if (Schema::hasColumn('sales', 'batche_id')) {
                $table->dropForeign(['batche_id']);
                $table->renameColumn('batche_id', 'batch_id');
            }
        });

        Schema::table('sales', function (Blueprint $table): void {
            if (! Schema::hasColumn('sales', 'stocking_id')) {
                // Link to the specific stocking event (which provides biomass)
                $table->uuid('stocking_id')->nullable()->after('batch_id');
                $table->foreign('stocking_id')->references('id')->on('stockings')->onDelete('set null');
            }

            if (! Schema::hasColumn('sales', 'financial_category_id')) {
                // Revenue category used to auto-generate the receivable
                $table->uuid('financial_category_id')->nullable()->after('stocking_id');
                $table->foreign('financial_category_id')
                    ->references('id')
                    ->on('financial_categories')
                    ->onDelete('set null');
            }

            if (! Schema::hasColumn('sales', 'status')) {
                $table->enum('status', ['pending', 'confirmed', 'cancelled'])
                    ->default('pending')
                    ->after('sale_date');
            }

            if (! Schema::hasColumn('sales', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });

        if ($hadBatcheColumn && Schema::hasColumn('sales', 'batch_id')) {
            Schema::table('sales', function (Blueprint $table): void {
                // Recreates FK only when column was renamed in this migration.
                $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            if (Schema::hasColumn('sales', 'stocking_id')) {
                $table->dropForeign(['stocking_id']);
            }

            if (Schema::hasColumn('sales', 'financial_category_id')) {
                $table->dropForeign(['financial_category_id']);
            }

            $columnsToDrop = array_values(array_filter([
                Schema::hasColumn('sales', 'stocking_id') ? 'stocking_id' : null,
                Schema::hasColumn('sales', 'financial_category_id') ? 'financial_category_id' : null,
                Schema::hasColumn('sales', 'status') ? 'status' : null,
                Schema::hasColumn('sales', 'notes') ? 'notes' : null,
            ]));

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }

            if (Schema::hasColumn('sales', 'batche_id') === false && Schema::hasColumn('sales', 'batch_id')) {
                $table->dropForeign(['batch_id']);
                $table->renameColumn('batch_id', 'batche_id');
                $table->foreign('batche_id')->references('id')->on('batches')->onDelete('cascade');
            }
        });
    }
};
