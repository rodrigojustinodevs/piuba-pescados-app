<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table): void {
            // Rename transaction_date → due_date (competência)
            $table->renameColumn('transaction_date', 'due_date');
        });

        Schema::table('financial_transactions', function (Blueprint $table): void {
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])
                ->default('pending')
                ->after('amount');

            $table->date('payment_date')->nullable()->after('due_date');

            $table->string('reference_type', 50)->nullable()->after('payment_date');
            $table->uuid('reference_id')->nullable()->after('reference_type');

            $table->text('notes')->nullable()->after('reference_id');

            // Index to speed up polymorphic look-ups
            $table->index(['reference_type', 'reference_id'], 'ft_reference_idx');
        });

        // Migrate legacy 'income' type to 'revenue'
        DB::table('financial_transactions')
            ->where('type', 'income')
            ->update(['type' => 'revenue']);

        DB::statement(
            "ALTER TABLE financial_transactions MODIFY COLUMN type ENUM('revenue','expense','investment') NOT NULL"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE financial_transactions MODIFY COLUMN type ENUM('income','expense') NOT NULL"
        );

        DB::table('financial_transactions')
            ->where('type', 'revenue')
            ->update(['type' => 'income']);

        Schema::table('financial_transactions', function (Blueprint $table): void {
            $table->dropIndex('ft_reference_idx');
            $table->dropColumn(['status', 'payment_date', 'reference_type', 'reference_id', 'notes']);
        });

        Schema::table('financial_transactions', function (Blueprint $table): void {
            $table->renameColumn('due_date', 'transaction_date');
        });
    }
};
