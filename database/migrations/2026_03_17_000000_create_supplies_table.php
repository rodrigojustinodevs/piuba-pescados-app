<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     * Catálogo de insumos por empresa (ração, químicos, etc.).
     */
    public function up(): void
    {
        Schema::create('supplies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->string('name', 255);
            $table->string('category')->nullable()->comment('Ex: Feed, Medicine, Equipment');
            $table->enum('default_unit', ['kg', 'g', 'liter', 'ml', 'unit', 'box', 'piece'])->default('kg')->comment('standard unit of measurement: kg, g, liter, ml, unit, box, piece');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->unique(['company_id', 'name'], 'supplies_company_id_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};
