<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Enums\FinancialTransactionStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Sincroniza o campo is_defaulter dos clientes com base nas transações financeiras.
 *
 * Marcação como inadimplente: cliente com pelo menos uma FinancialTransaction
 * com status = overdue vinculada via Sale.
 *
 * Remoção da flag: cliente sem nenhuma transação overdue em aberto.
 *
 * Agendamento sugerido: diário, via app/Console/Kernel ou schedule() no console.php.
 */
class MarkOverdueClientsCommand extends Command
{
    protected $signature = 'clients:mark-overdue
                            {--dry-run : Exibe os clientes afetados sem persistir alterações}';

    protected $description = 'Marca/desmarca clientes como inadimplentes com base em transações financeiras vencidas';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->markDefaulters($dryRun);
        $this->clearDefaulters($dryRun);

        $this->info('Sincronização de inadimplência concluída.');

        return self::SUCCESS;
    }

    /**
     * Marca como inadimplentes os clientes que possuem transações overdue vinculadas.
     */
    private function markDefaulters(bool $dryRun): void
    {
        $clientIds = DB::table('financial_transactions')
            ->join('sales', 'sales.id', '=', 'financial_transactions.reference_id')
            ->where('financial_transactions.reference_type', 'sale')
            ->where('financial_transactions.status', FinancialTransactionStatus::OVERDUE->value)
            ->whereNull('financial_transactions.deleted_at')
            ->whereNull('sales.deleted_at')
            ->pluck('sales.client_id')
            ->unique()
            ->values()
            ->all();

        if (empty($clientIds)) {
            $this->line('Nenhum cliente a marcar como inadimplente.');

            return;
        }

        $this->line(sprintf('Marcando %d cliente(s) como inadimplente(s)...', count($clientIds)));

        if (! $dryRun) {
            DB::table('clients')
                ->whereIn('id', $clientIds)
                ->whereNull('deleted_at')
                ->update(['is_defaulter' => true]);

            Log::info('clients:mark-overdue — clientes marcados como inadimplentes', ['ids' => $clientIds]);
        }
    }

    /**
     * Remove a flag de inadimplência dos clientes que não possuem mais transações overdue.
     */
    private function clearDefaulters(bool $dryRun): void
    {
        $clientIdsWithOverdue = DB::table('financial_transactions')
            ->join('sales', 'sales.id', '=', 'financial_transactions.reference_id')
            ->where('financial_transactions.reference_type', 'sale')
            ->where('financial_transactions.status', FinancialTransactionStatus::OVERDUE->value)
            ->whereNull('financial_transactions.deleted_at')
            ->whereNull('sales.deleted_at')
            ->pluck('sales.client_id')
            ->unique()
            ->all();

        $affected = DB::table('clients')
            ->where('is_defaulter', true)
            ->whereNull('deleted_at')
            ->when(! empty($clientIdsWithOverdue), fn ($q) => $q->whereNotIn('id', $clientIdsWithOverdue))
            ->pluck('id')
            ->all();

        if (empty($affected)) {
            $this->line('Nenhum cliente a remover da inadimplência.');

            return;
        }

        $this->line(sprintf('Removendo inadimplência de %d cliente(s)...', count($affected)));

        if (! $dryRun) {
            DB::table('clients')
                ->whereIn('id', $affected)
                ->update(['is_defaulter' => false]);

            Log::info('clients:mark-overdue — clientes removidos da inadimplência', ['ids' => $affected]);
        }
    }
}
