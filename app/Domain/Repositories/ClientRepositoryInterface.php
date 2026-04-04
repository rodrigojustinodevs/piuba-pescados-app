<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\ClientInputDTO;
use App\Domain\Models\Client;

interface ClientRepositoryInterface
{
    /**
     * Persiste um novo cliente a partir do DTO de entrada.
     */
    public function create(ClientInputDTO $dto): Client;

    /**
     * Update an existing financial transaction record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Client;

    /**
     * Delete a financial transaction record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate financial transaction records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a financial transaction by a specific field.
     */
    public function showClient(string $field, string | int $value): ?Client;

    /**
     * Verifica se o cliente possui vendas com transações financeiras pendentes ou em atraso.
     */
    public function hasPendingObligations(string $id): bool;

    /**
     * Anonimiza os dados sensíveis do cliente (LGPD), preservando id e nome.
     */
    public function anonymize(string $id): bool;

    /**
     * Find a client by ID or throw ModelNotFoundException.
     */
    public function findOrFail(string $id): Client;

    /**
     * Get the current exposure of the client.
     */
    public function getPendingObligations(string $id): float;
}
