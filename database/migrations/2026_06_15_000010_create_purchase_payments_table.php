<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_payments', static function (Blueprint $table): void {
            $table->char('id', 36)->primary();
            $table->char('purchase_id', 36)->index();
            $table->timestamp('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50);
            $table->string('reference', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('purchase_id')
                ->references('id')
                ->on('purchases')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};
