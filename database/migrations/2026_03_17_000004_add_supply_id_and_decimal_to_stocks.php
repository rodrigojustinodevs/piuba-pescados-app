<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class () extends Migration
{
    /**
     * Run the migrations.
     * Adiciona supply_id, converte quantidades/preços para DECIMAL e cria UNIQUE(company_id, supply_id).
     */
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table): void {
            $table->uuid('supply_id')->nullable()->after('company_id');

            if (! Schema::hasColumn('stocks', 'unit_price')) {
                $table->decimal('unit_price', 15, 4)->default(0)->after('current_quantity');
            }
        });

        $validUnits = ['kg', 'g', 'liter', 'ml', 'unit', 'box', 'piece'];
        $stocks     = DB::table('stocks')->get();

        foreach ($stocks as $stock) {
            $name        = 'Estoque legado ' . str_replace('-', '', substr((string) $stock->id, 0, 12));
            $supplyId    = (string) Str::uuid();
            $unit        = $stock->unit ?? 'kg';
            $defaultUnit = in_array($unit, $validUnits, true) ? $unit : 'kg';
            DB::table('supplies')->insert([
                'id'           => $supplyId,
                'company_id'   => $stock->company_id,
                'name'         => $name,
                'category'     => null,
                'default_unit' => $defaultUnit,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            DB::table('stocks')->where('id', $stock->id)->update(['supply_id' => $supplyId]);
        }

        DB::statement('ALTER TABLE stocks MODIFY supply_id CHAR(36) NOT NULL');
        Schema::table('stocks', function (Blueprint $table): void {
            $table->foreign('supply_id')->references('id')->on('supplies')->restrictOnDelete();
            $table->unique(['company_id', 'supply_id'], 'stocks_company_id_supply_id_unique');
        });

        $this->changeNumericColumnsToDecimal();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table): void {
            $table->dropUnique('stocks_company_id_supply_id_unique');
            $table->dropForeign(['supply_id']);
            $table->dropColumn('supply_id');
        });

        $this->revertDecimalToFloat();
    }

    private function changeNumericColumnsToDecimal(): void
    {
        $columns = [
            'current_quantity'    => 'DECIMAL(15, 4) NOT NULL',
            'minimum_stock'       => 'DECIMAL(15, 4) NOT NULL DEFAULT 0',
            'withdrawal_quantity' => 'DECIMAL(15, 4) NOT NULL DEFAULT 0',
        ];

        if (Schema::hasColumn('stocks', 'unit_price')) {
            $columns['unit_price'] = 'DECIMAL(15, 4) NOT NULL DEFAULT 0';
        }

        foreach ($columns as $col => $def) {
            if (Schema::hasColumn('stocks', $col)) {
                DB::statement("ALTER TABLE stocks MODIFY {$col} {$def}");
            }
        }
    }

    private function revertDecimalToFloat(): void
    {
        $columns = ['current_quantity', 'unit_price', 'minimum_stock', 'withdrawal_quantity'];

        foreach ($columns as $col) {
            if (Schema::hasColumn('stocks', $col)) {
                $nullable = $col === 'unit_price' ? 'NULL' : 'NOT NULL';
                $default  = $col === 'unit_price' ? ' DEFAULT NULL' : ' NOT NULL DEFAULT 0';
                DB::statement("ALTER TABLE stocks MODIFY {$col} DOUBLE {$nullable}{$default}");
            }
        }
    }
};
