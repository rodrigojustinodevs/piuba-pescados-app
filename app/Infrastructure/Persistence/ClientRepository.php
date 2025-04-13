<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Client;
use App\Domain\Repositories\ClientRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * Create a new financial category.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Client
    {
        return Client::create($data);
    }

    /**
     * Update an existing financial category.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Client
    {
        $client = Client::find($id);

        if ($client) {
            $client->update($data);

            return $client;
        }

        return null;
    }

    /**
     * Get paginated financial categories.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<Client> $paginator */
        $paginator = Client::with([
            'company:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show financial category by field and value.
     */
    public function showClient(string $field, string | int $value): ?Client
    {
        return Client::where($field, $value)->first();
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
}
