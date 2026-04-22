<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::create('user_company_permissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignUuid('permission_id')
                ->constrained('permissions')
                ->cascadeOnDelete();

            $table->enum('type', ['grant', 'deny'])
                ->default('grant')
                ->comment('grant = adiciona, deny = remove a permissão do role');

            $table->timestamps();

            $table->unique(['user_id', 'company_id', 'permission_id']);
            $table->index(['user_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_company_permissions');
    }
};
