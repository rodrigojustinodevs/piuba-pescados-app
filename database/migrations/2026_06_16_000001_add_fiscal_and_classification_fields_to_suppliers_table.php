<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table): void {
            $table->string('trade_name', 255)->nullable()->after('name');
            $table->string('document', 14)->nullable()->after('email');
            $table->index('document');
            $table->string('state_registration', 30)->nullable()->after('document');
            $table->string('category', 20)->default('other')->after('state_registration');
            $table->string('payment_terms', 255)->nullable()->after('category');
            $table->decimal('rating', 2, 1)->default(0)->after('payment_terms');
            $table->string('status', 20)->default('active')->after('rating');
            $table->jsonb('address')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table): void {
            $table->dropIndex(['document']);
            $table->dropColumn([
                'trade_name',
                'document',
                'state_registration',
                'category',
                'payment_terms',
                'rating',
                'status',
                'address',
            ]);
        });
    }
};
