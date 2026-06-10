<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('transfers', 'batche_id')) {
            Schema::table('transfers', function (Blueprint $table): void {
                $table->renameColumn('batche_id', 'batch_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transfers', 'batch_id')) {
            Schema::table('transfers', function (Blueprint $table): void {
                $table->renameColumn('batch_id', 'batche_id');
            });
        }
    }
};
