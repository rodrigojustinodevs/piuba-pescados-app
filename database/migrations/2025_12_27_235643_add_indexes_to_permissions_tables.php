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
        Schema::table('roles', function (Blueprint $table): void {
            $table->index('name', 'idx_roles_name');
        });

        Schema::table('permissions', function (Blueprint $table): void {
            $table->index('name', 'idx_permissions_name');
        });

        Schema::table('role_user', function (Blueprint $table): void {
            $table->index('user_id', 'idx_role_user_user_id');
        });

        Schema::table('permission_user', function (Blueprint $table): void {
            $table->index('user_id', 'idx_permission_user_user_id');
        });

        Schema::table('permission_role', function (Blueprint $table): void {
            $table->index('role_id', 'idx_permission_role_role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropIndex('idx_roles_name');
        });

        Schema::table('permissions', function (Blueprint $table): void {
            $table->dropIndex('idx_permissions_name');
        });

        Schema::table('role_user', function (Blueprint $table): void {
            $table->dropIndex('idx_role_user_user_id');
        });

        Schema::table('permission_user', function (Blueprint $table): void {
            $table->dropIndex('idx_permission_user_user_id');
        });

        Schema::table('permission_role', function (Blueprint $table): void {
            $table->dropIndex('idx_permission_role_role_id');
        });
    }
};
