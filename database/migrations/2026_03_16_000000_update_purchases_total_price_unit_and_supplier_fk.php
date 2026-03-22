<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE purchases MODIFY total_price DECIMAL(15, 2) NOT NULL');

        Schema::table('purchases', function (Blueprint $table): void {
            $table->string('unit', 50)->after('quantity')->default('kg');
        });

        $this->ensureSupplierForeignKey();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table): void {
            $table->dropColumn('unit');
        });

        DB::statement('ALTER TABLE purchases MODIFY total_price DOUBLE NOT NULL');
    }

    private function ensureSupplierForeignKey(): void
    {
        $fkExists = DB::selectOne(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'purchases'
             AND CONSTRAINT_TYPE = 'FOREIGN KEY'
             AND CONSTRAINT_NAME LIKE '%supplier%'",
            [config('database.connections.mysql.database')]
        );

        if (! $fkExists) {
            Schema::table('purchases', function (Blueprint $table): void {
                $table->foreign('supplier_id')->references('id')->on('suppliers');
            });
        }
    }
};
