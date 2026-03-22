<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_adjustments', static function (Blueprint $table): void {
            // ── Identificação ────────────────────────────────────────────────
            $table->uuid('id')->primary();

            // ── Relacionamentos obrigatórios ─────────────────────────────────
            $table->foreignUuid('stock_id')
                ->constrained('stocks')
                ->cascadeOnDelete(); // ajuste some se o stock for excluído

            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->restrictOnDelete();

            $table->foreignUuid('user_id')
                ->comment('Usuário que realizou a contagem física')
                ->constrained('users')
                ->restrictOnDelete();

            // ── Dados da contagem física ─────────────────────────────────────
            $table->decimal('previous_quantity', 12, 4)
                ->comment('Saldo no sistema antes do ajuste');

            $table->decimal('new_quantity', 12, 4)
                ->comment('Quantidade física contada');

            $table->decimal('adjusted_quantity', 12, 4)
                ->comment('Delta com sinal: positivo = ganho, negativo = perda');

            $table->string('unit', 20)
                ->comment('Unidade de medida herdada do stock no momento do ajuste');

            // ── Snapshot financeiro (PMP no momento do ajuste) ───────────────
            $table->decimal('unit_price', 12, 2)
                ->comment('Preço Médio Ponderado no momento do ajuste — imutável após criação');

            // ── Status do documento ──────────────────────────────────────────
            $table->string('status', 20)
                ->default('pending')
                ->comment('pending | applied | cancelled');

            // ── Rastreabilidade ──────────────────────────────────────────────
            $table->text('reason')
                ->nullable()
                ->comment('Motivo do ajuste: saco rasgado, desvio, erro de contagem, etc.');

            // Fecha o ciclo bidirecional com stock_transactions.
            // Preenchido logo após a transação ser criada via linkTransaction().
            // nullable porque o INSERT inicial não conhece ainda o id da transação.
            // nullOnDelete: se a transação for deletada, o documento de ajuste
            // sobrevive mas perde a referência — não quebra a integridade.
            $table->foreignUuid('reference_transaction_id')
                ->nullable()
                ->comment('FK para stock_transactions — preenchida após criação da transação')
                ->constrained('stock_transactions')
                ->nullOnDelete();

            // ── Timestamps e soft delete ─────────────────────────────────────
            $table->timestamps();
            $table->softDeletes();

            // ── Índices ──────────────────────────────────────────────────────
            // Consultas mais comuns: ajustes por stock + filtro de status
            $table->index(['stock_id', 'status'], 'ia_stock_status_idx');

            // Listagem por empresa com paginação
            $table->index(['company_id', 'created_at'], 'ia_company_created_idx');

            // Auditoria por usuário
            $table->index('user_id', 'ia_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
