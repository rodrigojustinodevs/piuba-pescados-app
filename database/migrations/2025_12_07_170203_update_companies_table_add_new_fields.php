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
        Schema::table('companies', function (Blueprint $table): void {
            $table->string('email', 255)->nullable()->after('cnpj');
            $table->string('address_street', 255)->nullable()->after('email');
            $table->string('address_number', 50)->nullable()->after('address_street');
            $table->string('address_complement', 255)->nullable()->after('address_number');
            $table->string('address_neighborhood', 255)->nullable()->after('address_complement');
            $table->string('address_city', 255)->nullable()->after('address_neighborhood');
            $table->string('address_state', 2)->nullable()->after('address_city');
            $table->string('address_zip_code', 20)->nullable()->after('address_state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn([
                'email',
                'address_street',
                'address_number',
                'address_complement',
                'address_neighborhood',
                'address_city',
                'address_state',
                'address_zip_code',
            ]);
        });
    }
};
