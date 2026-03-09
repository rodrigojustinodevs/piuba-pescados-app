<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     * Rename table feed_control to feed_inventory (controle de estoque de ração).
     */
    public function up(): void
    {
        Schema::rename('feed_control', 'feed_inventory');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('feed_inventory', 'feed_control');
    }
};
