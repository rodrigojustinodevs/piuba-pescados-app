<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('transfers', function (Blueprint $table): void {
            if (! Schema::hasColumn('transfers', 'company_id')) {
                $table->uuid('company_id')
                    ->nullable()
                    ->after('id');

                $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->onDelete('cascade');
            }

            if (! Schema::hasColumn('transfers', 'child_batch_id')) {
                $table->uuid('child_batch_id')
                    ->nullable()
                    ->after('batch_id')
                    ->comment('Sub-lote criado no destino em transferências parciais');

                $table->foreign('child_batch_id')
                    ->references('id')
                    ->on('batches')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transfers', function (Blueprint $table): void {
            $table->dropForeign(['child_batch_id']);
            $table->dropColumn('child_batch_id');

            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
