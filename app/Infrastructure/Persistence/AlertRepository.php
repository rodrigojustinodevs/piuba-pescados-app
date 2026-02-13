<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Alert;
use App\Domain\Repositories\AlertRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

class AlertRepository implements AlertRepositoryInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Alert
    {
        return Alert::create($data);
    }

    public function findById(string $id): ?Alert
    {
        return Alert::find($id);
    }

    /**
     * Get paginated .
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var \Illuminate\Pagination\LengthAwarePaginator<int, Alert> $paginator */
        $paginator = Alert::with([
            'company:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Alert
    {
        $alert = $this->findById($id);

        if (! $alert instanceof Alert) {
            return null;
        }

        $alert->update($data);

        return $alert;
    }

    public function delete(string $id): bool
    {
        $alert = $this->findById($id);

        if (! $alert instanceof Alert) {
            return false;
        }

        return (bool) $alert->delete();
    }
}
