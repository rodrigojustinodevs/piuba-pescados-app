<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('role')->comment('RolesEnum value');
            $table->foreignUuid('permission_id')
                ->constrained('permissions')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role', 'permission_id']);
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
