<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table): void {
            $table->string('code', 100)->nullable()->after('company_id');
            $table->string('name', 255)->nullable()->after('code');
            $table->enum('type', ['warehouse', 'cold_room', 'silo', 'storage', 'field'])->nullable()->after('name');
            $table->string('location', 255)->nullable()->after('type');
            $table->string('responsible', 255)->nullable()->after('location');
            $table->decimal('capacity', 15, 3)->nullable()->after('responsible');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('capacity');
            $table->text('notes')->nullable()->after('status');

            $table->unique(['company_id', 'code'], 'stocks_company_code_unique');
            $table->index('type', 'stocks_type_index');
            $table->index('status', 'stocks_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table): void {
            $table->dropUnique('stocks_company_code_unique');
            $table->dropIndex('stocks_type_index');
            $table->dropIndex('stocks_status_index');
            $table->dropColumn(['code', 'name', 'type', 'location', 'responsible', 'capacity', 'status', 'notes']);
        });
    }
};
