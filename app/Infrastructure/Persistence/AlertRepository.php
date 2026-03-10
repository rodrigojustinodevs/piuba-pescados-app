<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Alert;
use App\Domain\Models\Batch;
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

    public function createHighFcrAlert(Batch $batch, float $fcr, float $threshold): Alert
    {
        $companyId = $batch->tank->company_id ?? '';

        return $this->create([
            'company_id' => $companyId,
            'alert_type' => 'high_fcr',
            'message'    => sprintf(
                'Lote %s com FCR %.2f acima do limite (%.2f).',
                $batch->name ?? $batch->id,
                $fcr,
                $threshold
            ),
            'status' => 'pending',
        ]);
    }

    public function createDensityAlert(Batch $batch, float $density, float $threshold): Alert
    {
        $companyId = $batch->tank->company_id ?? '';

        return $this->create([
            'company_id' => $companyId,
            'alert_type' => 'density',
            'message'    => sprintf(
                'Lote %s com densidade %.2f acima do limite (%.2f).',
                $batch->name ?? $batch->id,
                $density,
                $threshold
            ),
            'status' => 'pending',
        ]);
    }

    public function createRationDeviationAlert(
        Batch $batch,
        float $quantityProvided,
        float $recommendedRation
    ): Alert {
        $companyId = $batch->tank->company_id ?? '';

        return $this->create([
            'company_id' => $companyId,
            'alert_type' => 'ration_deviation',
            'message'    => sprintf(
                'Lote %s: ração fornecida (%.2f kg) difere da recomendada (%.2f kg).',
                $batch->name ?? $batch->id,
                $quantityProvided,
                $recommendedRation
            ),
            'status' => 'pending',
        ]);
    }
}
