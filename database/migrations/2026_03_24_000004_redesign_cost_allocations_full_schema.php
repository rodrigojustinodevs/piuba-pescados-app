<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        // ── 1. Update cost_allocations ────────────────────────────────────────
        Schema::table('cost_allocations', function (Blueprint $table): void {
            // Keep id, company_id, timestamps, softDeletes
            // Rename legacy fields to new semantic names
            if (Schema::hasColumn('cost_allocations', 'description')) {
                $table->renameColumn('description', 'notes');
            }

            if (Schema::hasColumn('cost_allocations', 'amount')) {
                $table->renameColumn('amount', 'total_amount');
            }

            if (Schema::hasColumn('cost_allocations', 'registration_date')) {
                $table->dropColumn('registration_date');
            }

            $table->uuid('financial_transaction_id')
                ->nullable()
                ->after('company_id');

            $table->enum('allocation_method', ['flat', 'biomass', 'volume'])
                ->after('financial_transaction_id');

            $table->foreign('financial_transaction_id')
                ->references('id')
                ->on('financial_transactions')
                ->onDelete('set null');
        });

        // ── 2. Create cost_allocation_items ───────────────────────────────────
        Schema::create('cost_allocation_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('cost_allocation_id');
            $table->uuid('stocking_id');
            $table->decimal('percentage', 8, 4);
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->foreign('cost_allocation_id')
                ->references('id')
                ->on('cost_allocations')
                ->onDelete('cascade');

            $table->foreign('stocking_id')
                ->references('id')
                ->on('stockings')
                ->onDelete('cascade');
        });

        // ── 3. Add is_allocated to financial_transactions ─────────────────────
        Schema::table('financial_transactions', function (Blueprint $table): void {
            $table->boolean('is_allocated')
                ->default(false)
                ->after('notes');
        });

        // ── 4. Add accumulated_fixed_cost to stockings ────────────────────────
        Schema::table('stockings', function (Blueprint $table): void {
            $table->decimal('accumulated_fixed_cost', 15, 2)
                ->default(0)
                ->after('average_weight');
        });
    }

    public function down(): void
    {
        Schema::table('stockings', function (Blueprint $table): void {
            $table->dropColumn('accumulated_fixed_cost');
        });

        Schema::table('financial_transactions', function (Blueprint $table): void {
            $table->dropColumn('is_allocated');
        });

        Schema::dropIfExists('cost_allocation_items');

        Schema::table('cost_allocations', function (Blueprint $table): void {
            $table->dropForeign(['financial_transaction_id']);
            $table->dropColumn(['financial_transaction_id', 'allocation_method']);
            $table->renameColumn('notes', 'description');
            $table->renameColumn('total_amount', 'amount');
            $table->date('registration_date')->nullable();
        });
    }
};
