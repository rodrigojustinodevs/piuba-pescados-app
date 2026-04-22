<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->string('document', 20)->nullable()->after('cnpj')->comment('CNPJ/CPF');
            $table->string('slug', 255)->nullable()->after('name');
            $table->json('settings')->nullable()->after('phone')->comment('Configurações específicas da empresa');
            $table->boolean('is_active')->default(true)->after('status');
            $table->timestamp('trial_ends_at')->nullable()->after('is_active');

            $table->index('slug');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->dropIndex(['slug']);
            $table->dropIndex(['is_active']);

            $table->dropColumn([
                'document',
                'slug',
                'settings',
                'is_active',
                'trial_ends_at',
            ]);
        });
    }
};
