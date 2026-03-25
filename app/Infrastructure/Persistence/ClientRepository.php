<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\ClientInputDTO;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Models\Client;
use App\Domain\Repositories\ClientRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * Persiste um novo cliente a partir do DTO de entrada.
     */
    public function create(ClientInputDTO $dto): Client
    {
        /** @var Client $client */
        $client = Client::create($dto->toPersistence());

        return $client->load('company');
    }

    /**
     * Atualiza um cliente e retorna o model atualizado com a relation company.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Client
    {
        $client = Client::find($id);

        if (! $client) {
            return null;
        }

        $client->update($data);

        return $client->loadMissing('company:id,name');
    }

    /**
     * Get paginated financial categories.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<int, Client> $paginator */
        $paginator = Client::with([
            'company:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Busca um cliente por campo/valor com a relation company carregada.
     */
    public function showClient(string $field, string | int $value): ?Client
    {
        return Client::with('company:id,name')->where($field, $value)->first();
    }

    /**
     * Delete a financial category.
     */
    public function delete(string $id): bool
    {
        $client = Client::find($id);

        if (! $client) {
            return false;
        }

        return (bool) $client->delete();
    }

    /**
     * Verifica se o cliente possui vendas com transações financeiras pendentes ou em atraso.
     * O vínculo é: Client → Sales → FinancialTransactions (reference_type=sale, reference_id=sale.id).
     */
    public function hasPendingObligations(string $id): bool
    {
        return DB::table('financial_transactions')
            ->join('sales', 'sales.id', '=', 'financial_transactions.reference_id')
            ->where('sales.client_id', $id)
            ->where('financial_transactions.reference_type', 'sale')
            ->whereIn('financial_transactions.status', [
                FinancialTransactionStatus::PENDING->value,
                FinancialTransactionStatus::OVERDUE->value,
            ])
            ->whereNull('financial_transactions.deleted_at')
            ->whereNull('sales.deleted_at')
            ->exists();
    }

    /**
     * Anonimiza os dados sensíveis do cliente (LGPD).
     * Preserva id e nome para manter o histórico financeiro íntegro.
     */
    public function anonymize(string $id): bool
    {
        $client = Client::find($id);

        if (! $client) {
            return false;
        }

        $client->email           = null;
        $client->phone           = null;
        $client->address         = null;
        $client->contact         = null;
        $client->document_number = '[ANONIMIZADO]';

        return $client->save();
    }
}
