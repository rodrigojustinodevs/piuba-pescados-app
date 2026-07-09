<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            $table->string('code', 20)->nullable()->unique()->after('id');
            $table->string('invoice_number', 50)->nullable()->after('financial_category_id');
            $table->uuid('responsible_user_id')->nullable()->after('invoice_number');
            $table->foreign('responsible_user_id')->references('id')->on('users')->onDelete('set null');
            $table->decimal('discount', 10, 2)->default(0)->after('total_revenue');
            $table->decimal('shipping', 10, 2)->default(0)->after('discount');
            $table->decimal('taxes', 10, 2)->default(0)->after('shipping');
            $table->date('due_date')->nullable()->after('sale_date');
            $table->date('paid_date')->nullable()->after('due_date');
            $table->timestamp('delivered_at')->nullable()->after('paid_date');
            $table->string('payment_method', 30)->nullable()->after('delivered_at');
        });

        // Expand status enum to include paid, delivered, overdue
        DB::statement("ALTER TABLE sales MODIFY COLUMN status ENUM('pending','confirmed','paid','delivered','overdue','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Revert status enum
        DB::statement("ALTER TABLE sales MODIFY COLUMN status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('sales', function (Blueprint $table): void {
            $table->dropForeign(['responsible_user_id']);
            $table->dropColumn([
                'code',
                'invoice_number',
                'responsible_user_id',
                'discount',
                'shipping',
                'taxes',
                'due_date',
                'paid_date',
                'delivered_at',
                'payment_method',
            ]);
        });
    }
};
