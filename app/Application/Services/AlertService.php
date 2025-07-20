<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\AlertDTO;
use App\Application\UseCases\Alert\CreateAlertUseCase;
use App\Application\UseCases\Alert\DeleteAlertUseCase;
use App\Application\UseCases\Alert\ListAlertsUseCase;
use App\Application\UseCases\Alert\ShowAlertUseCase;
use App\Application\UseCases\Alert\UpdateAlertUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlertService
{
    public function __construct(
        protected CreateAlertUseCase $createAlertUseCase,
        protected ListAlertsUseCase $listAlertsUseCase,
        protected ShowAlertUseCase $showAlertUseCase,
        protected UpdateAlertUseCase $updateAlertUseCase,
        protected DeleteAlertUseCase $deleteAlertUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): AlertDTO
    {
        return $this->createAlertUseCase->execute($data);
    }

    public function showAllAlerts(): AnonymousResourceCollection
    {
        return $this->listAlertsUseCase->execute();
    }

    public function showAlert(string $id): ?AlertDTO
    {
        return $this->showAlertUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateAlert(string $id, array $data): AlertDTO
    {
        return $this->updateAlertUseCase->execute($id, $data);
    }

    public function deleteAlert(string $id): bool
    {
        return $this->deleteAlertUseCase->execute($id);
    }
}
