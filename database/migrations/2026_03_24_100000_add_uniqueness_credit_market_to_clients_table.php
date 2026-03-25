<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->decimal('credit_limit', 10, 2)->nullable()->after('address');
            $table->boolean('is_defaulter')->default(false)->after('credit_limit');
            $table->enum('price_group', ['wholesale', 'retail', 'consumer'])->nullable()->after('is_defaulter');

            $table->unique(['company_id', 'document_number'], 'clients_company_document_unique');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropUnique('clients_company_document_unique');
            $table->dropColumn(['credit_limit', 'is_defaulter', 'price_group']);
        });
    }
};
