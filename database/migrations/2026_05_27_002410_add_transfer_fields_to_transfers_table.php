<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('transfers', function (Blueprint $table): void {
            $table->date('transfer_date')
                ->nullable()
                ->after('id');

            $table->enum('status', [
                'completed',
                'scheduled',
                'cancelled',
            ])
                ->default('scheduled')
                ->after('transfer_date');

            $table->enum('reason', [
                'growth',
                'density',
                'biosecurity',
                'maintenance',
                'harvest_prep',
                'other',
            ])
                ->nullable()
                ->after('status');

            $table->string('responsible')
                ->nullable()
                ->after('reason');

            $table->decimal('average_weight', 10, 2)
                ->nullable()
                ->comment('grams')
                ->after('responsible');
        });
    }

    public function down(): void
    {
        Schema::table('transfers', function (Blueprint $table): void {
            $table->dropColumn([
                'transfer_date',
                'status',
                'reason',
                'responsible',
                'average_weight',
            ]);
        });
    }
};
