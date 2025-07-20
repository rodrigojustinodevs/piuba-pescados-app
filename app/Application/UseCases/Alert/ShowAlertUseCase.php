<?php

declare(strict_types=1);

namespace App\Application\UseCases\Alert;

use App\Application\DTOs\AlertDTO;
use App\Domain\Models\Alert;
use App\Domain\Repositories\AlertRepositoryInterface;
use RuntimeException;

class ShowAlertUseCase
{
    public function __construct(protected AlertRepositoryInterface $repository)
    {
    }

    public function execute(string $id): ?AlertDTO
    {
        $alert = $this->repository->findById($id);

        if (! $alert instanceof Alert) {
            throw new RuntimeException('Alert not found');
        }

        return new AlertDTO(
            id: $alert->id,
            alertType: $alert->alert_type,
            message: $alert->message,
            status: $alert->status,
            company: [
                'name' => $alert->company->name ?? '',
            ],
            createdAt: (string)$alert->created_at?->toDateTimeString(),
            updatedAt: $alert->updated_at?->toDateTimeString(),
        );
    }
}
