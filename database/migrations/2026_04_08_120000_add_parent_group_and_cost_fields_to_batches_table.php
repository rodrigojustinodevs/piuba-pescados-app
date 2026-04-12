<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('batches')) {
            return;
        }

        Schema::table('batches', function (Blueprint $table): void {
            if (! Schema::hasColumn('batches', 'parent_group_id')) {
                $table->uuid('parent_group_id')->nullable()->after('id');
                $table->index('parent_group_id', 'batches_parent_group_id_index');
            }

            if (! Schema::hasColumn('batches', 'unit_cost')) {
                $table->decimal('unit_cost', 15, 2)->default(0.00)->after('initial_quantity');
            }

            if (! Schema::hasColumn('batches', 'total_cost')) {
                $table->decimal('total_cost', 15, 2)->default(0.00)->after('unit_cost');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('batches')) {
            return;
        }

        Schema::table('batches', function (Blueprint $table): void {
            if (Schema::hasColumn('batches', 'total_cost')) {
                $table->dropColumn('total_cost');
            }

            if (Schema::hasColumn('batches', 'unit_cost')) {
                $table->dropColumn('unit_cost');
            }

            if (Schema::hasColumn('batches', 'parent_group_id')) {
                $table->dropIndex('batches_parent_group_id_index');
                $table->dropColumn('parent_group_id');
            }
        });
    }
};
