<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar se a coluna address existe antes de remover
        if (Schema::hasColumn('companies', 'address')) {
            Schema::table('companies', function (Blueprint $table): void {
                $table->dropColumn('address');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->text('address')->after('cnpj');
        });
    }
};
