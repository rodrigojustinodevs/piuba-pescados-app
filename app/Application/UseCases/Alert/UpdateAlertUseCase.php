<?php

declare(strict_types=1);

namespace App\Application\UseCases\Alert;

use App\Application\DTOs\AlertDTO;
use App\Domain\Models\Alert;
use App\Domain\Repositories\AlertRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateAlertUseCase
{
    public function __construct(protected AlertRepositoryInterface $repository)
    {
    }

    /**
     * @param array<string, mixed> $data
     * @throws RuntimeException
     */
    public function execute(string $id, array $data): AlertDTO
    {
        return DB::transaction(function () use ($id, $data): AlertDTO {
            $alert = $this->repository->update($id, $data);

            if (! $alert instanceof Alert) {
                throw new RuntimeException('Alert not found');
            }
            $alertArray            = $alert->toArray();
            $alertArray['company'] = $alert->company ?? null;

            return AlertDTO::fromArray($alertArray);
        });
    }
}
