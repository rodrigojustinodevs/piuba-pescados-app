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
        $driver = Schema::getConnection()->getDriverName();

        // 1. Alterar total_price para decimal(15, 2) (SQL nativo para não depender de doctrine/dbal)
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE purchases MODIFY total_price DECIMAL(15, 2) NOT NULL');
        }
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE purchases ALTER COLUMN total_price TYPE DECIMAL(15, 2)');
        }

        Schema::table('purchases', function (Blueprint $table): void {
            // 2. Adicionar a coluna unit (unidade de medida) após quantity
            $table->string('unit', 50)->after('quantity')->default('kg');
        });

        // 3. Garantir a constraint do fornecedor (criar apenas se não existir)
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

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE purchases MODIFY total_price DOUBLE NOT NULL');
        }
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE purchases ALTER COLUMN total_price TYPE DOUBLE PRECISION');
        }

        // Nota: não removemos a FK de supplier no down(), pois ela foi criada na migration original.
        // Se precisar remover, descomente e ajuste o nome da constraint conforme seu banco.
        // $this->dropSupplierForeignKeyIfExists();
    }

    private function ensureSupplierForeignKey(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $fkExists = DB::selectOne(
                "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'purchases'
                 AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                 AND CONSTRAINT_NAME LIKE '%supplier%'",
                [config('database.connections.mysql.database')]
            );
            if (!$fkExists) {
                Schema::table('purchases', function (Blueprint $table): void {
                    $table->foreign('supplier_id')->references('id')->on('suppliers');
                });
            }
        }

        if ($driver === 'pgsql') {
            $fkExists = DB::selectOne(
                "SELECT 1 FROM information_schema.table_constraints
                 WHERE table_schema = 'public' AND table_name = 'purchases'
                 AND constraint_type = 'FOREIGN KEY'
                 AND constraint_name LIKE '%supplier%'"
            );
            if (!$fkExists) {
                Schema::table('purchases', function (Blueprint $table): void {
                    $table->foreign('supplier_id')->references('id')->on('suppliers');
                });
            }
        }
    }
};
