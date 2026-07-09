<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('financial_transactions', static function (Blueprint $table): void {
            $table->uuid('financial_category_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('financial_transactions', static function (Blueprint $table): void {
            $table->uuid('financial_category_id')->nullable(false)->change();
        });
    }
};
