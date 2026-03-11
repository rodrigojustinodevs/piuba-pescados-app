<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('mortalities', function (Blueprint $table): void {
            $table->date('mortality_date')->after('cause');
        });
    }

    public function down(): void
    {
        Schema::table('mortalities', function (Blueprint $table): void {
            $table->dropColumn('mortality_date');
        });
    }
};
