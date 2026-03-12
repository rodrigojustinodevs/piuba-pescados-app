<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table): void {
            if (Schema::hasColumn('stocks', 'withdrawn_quantity')) {
                $table->renameColumn('withdrawn_quantity', 'withdrawal_quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table): void {
            if (Schema::hasColumn('stocks', 'withdrawal_quantity')) {
                $table->renameColumn('withdrawal_quantity', 'withdrawn_quantity');
            }
        });
    }
};

