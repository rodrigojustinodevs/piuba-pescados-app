<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        // ── 1. harvests: rename typo column and add despesca fields ──────────
        Schema::table('harvests', function (Blueprint $table): void {
            if (Schema::hasColumn('harvests', 'batche_id')) {
                $table->renameColumn('batche_id', 'batch_id');
            }

            $table->uuid('tank_id')->nullable()->after('batch_id');

            $table->enum('type', ['total', 'partial', 'selective', 'emergency'])
                ->default('partial')
                ->after('harvest_date');

            $table->enum('status', ['completed', 'scheduled', 'in_progress', 'cancelled'])
                ->default('completed')
                ->after('type');

            $table->enum('destination', ['wholesale', 'retail', 'processing', 'restaurant', 'live_market', 'internal'])
                ->nullable()
                ->after('status');

            $table->unsignedInteger('initial_population')->default(0)->after('destination');
            $table->unsignedInteger('harvested_quantity')->default(0)->after('initial_population');
            $table->float('average_weight')->default(0)->after('harvested_quantity');

            $table->string('client_destination')->nullable()->after('average_weight');
            $table->string('responsible')->nullable()->after('client_destination');
            $table->float('operational_cost')->default(0)->after('responsible');

            $table->foreign('tank_id')->references('id')->on('tanks')->onDelete('set null');
        });

        // ── 2. harvest_size_classifications: new table ───────────────────────
        Schema::create('harvest_size_classifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('harvest_id');
            $table->string('class', 10);
            $table->unsignedInteger('quantity');
            $table->float('average_weight');
            $table->float('price_per_kg');
            $table->timestamps();

            $table->foreign('harvest_id')
                ->references('id')
                ->on('harvests')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvest_size_classifications');

        Schema::table('harvests', function (Blueprint $table): void {
            $table->dropForeign(['tank_id']);
            $table->dropColumn([
                'tank_id',
                'type',
                'status',
                'destination',
                'initial_population',
                'harvested_quantity',
                'average_weight',
                'client_destination',
                'responsible',
                'operational_cost',
            ]);

            if (Schema::hasColumn('harvests', 'batch_id')) {
                $table->renameColumn('batch_id', 'batche_id');
            }
        });
    }
};
