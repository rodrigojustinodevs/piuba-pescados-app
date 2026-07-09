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
            $table->string('trade_name')->nullable()->after('name');
            $table->string('city')->nullable()->after('address');
            $table->string('state', 2)->nullable()->after('city');
            $table->string('status')->default('active')->after('state');
            $table->text('notes')->nullable()->after('status');
            $table->index('document_number');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropIndex(['document_number']);
            $table->dropColumn(['trade_name', 'city', 'state', 'status', 'notes']);
        });
    }
};
