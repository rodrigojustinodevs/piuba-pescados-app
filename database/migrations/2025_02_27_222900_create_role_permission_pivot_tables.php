<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Formaliza `role_user` e `permission_role` como migrations reais.
 *
 * Essas tabelas sustentam `User::isMasterAdmin()` (via `roles()`), chamado em
 * toda request protegida, e são referenciadas por uma migration existente
 * (`2026_04_11_110000_add_sales_order_permissions`). Nunca haviam sido
 * criadas por uma migration no repositório — existiam apenas por drift em
 * ambientes já provisionados, o que quebra `migrate:fresh`/`db:seed` em
 * qualquer ambiente novo (CI, clone, staging). Guardado com `hasTable` para
 * ser idempotente em bases onde as tabelas já existem via drift.
 */
return new class () extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('role_user')) {
            Schema::create('role_user', function (Blueprint $table): void {
                $table->foreignUuid('role_id')->constrained('roles')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->primary(['role_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table): void {
                $table->foreignUuid('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->foreignUuid('role_id')->constrained('roles')->cascadeOnDelete();
                $table->primary(['permission_id', 'role_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
    }
};
