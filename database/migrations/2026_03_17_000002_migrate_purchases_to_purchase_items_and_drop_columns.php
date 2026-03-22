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
     * Cria supplies a partir de item_name distintos, preenche purchase_items e remove colunas antigas de purchases.
     */
    public function up(): void
    {

        $distinctItems = DB::table('purchases')
            ->select('company_id', 'item_name')
            ->distinct()
            ->get();

        $supplyByKey = [];
        foreach ($distinctItems as $row) {
            $key = $row->company_id . '|' . $row->item_name;
            if (isset($supplyByKey[$key])) {
                continue;
            }
            $id = (string) Str::uuid();
            DB::table('supplies')->insert([
                'id' => $id,
                'company_id' => $row->company_id,
                'name' => $row->item_name,
                'category' => null,
                'default_unit' => 'kg',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $supplyByKey[$key] = $id;
        }

        $purchases = DB::table('purchases')->get();
        foreach ($purchases as $purchase) {
            $key = $purchase->company_id . '|' . $purchase->item_name;
            $supplyId = $supplyByKey[$key] ?? null;
            if (!$supplyId) {
                continue;
            }
            $quantity = (float) $purchase->quantity;
            $totalPrice = (float) $purchase->total_price;
            $unitPrice = $quantity > 0 ? $totalPrice / $quantity : 0;
            DB::table('purchase_items')->insert([
                'id' => (string) Str::uuid(),
                'purchase_id' => $purchase->id,
                'supply_id' => $supplyId,
                'quantity' => $quantity,
                'unit' => $purchase->unit ?? 'kg',
                'unit_price' => round($unitPrice, 4),
                'total_price' => round($totalPrice, 2),
                'created_at' => $purchase->created_at ?? now(),
                'updated_at' => $purchase->updated_at ?? now(),
            ]);
        }

        Schema::table('purchases', function (Blueprint $table): void {
            $table->dropForeign(['stocking_id']);
            $table->dropColumn(['stocking_id', 'item_name', 'quantity', 'unit']);
        });

        DB::table('purchases')->where('status', 'draft')->update(['status' => 'received']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('purchases', 'item_name')) {
            Schema::table('purchases', function (Blueprint $table): void {
                $table->string('item_name')->nullable()->after('stocking_id');
                $table->foreign('stocking_id')->references('id')->on('stockings');
                $table->float('quantity')->nullable()->after('item_name');
                $table->string('unit', 50)->default('kg')->after('quantity');
            });
        }

        $items = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('supplies', 'purchase_items.supply_id', '=', 'supplies.id')
            ->select('purchases.id as purchase_id', 'supplies.name as item_name', 'purchase_items.quantity', 'purchase_items.unit', 'purchase_items.total_price')
            ->orderBy('purchase_items.created_at')
            ->get();

        foreach ($items as $item) {
            DB::table('purchases')->where('id', $item->purchase_id)->update([
                'item_name' => $item->item_name,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'total_price' => $item->total_price,
            ]);
        }
    }
};
