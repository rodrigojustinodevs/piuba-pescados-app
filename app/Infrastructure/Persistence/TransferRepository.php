<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Transfer;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\TransferRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class TransferRepository implements TransferRepositoryInterface
{
    /**
     * Create a new transfer.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Transfer
    {
        return Transfer::create($data);
    }

    /**
     * Update an existing transfer.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Transfer
    {
        $transfer = Transfer::find($id);

        if ($transfer) {
            $transfer->update($data);

            return $transfer;
        }

        return null;
    }

    /**
     * Get paginated .
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<int, Transfer> $paginator */
        $paginator = Transfer::with([
            'batche:id',
            'originTank:id,name',
            'destinationTank:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show transfer by field and value.
     */
    public function showTransfer(string $field, string | int $value): ?Transfer
    {
        return Transfer::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $transfer = Transfer::find($id);

        if (! $transfer) {
            return false;
        }

        return (bool) $transfer->delete();
    }
}
