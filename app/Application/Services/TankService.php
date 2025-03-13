<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\TankDTO;
use App\Domain\Enums\Status;
use App\Domain\Models\Tank;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Presentation\Resources\Tank\TankResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TankService
{
    public function __construct(
        protected TankRepositoryInterface $tankRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): TankDTO
    {
        return DB::transaction(function () use ($data): TankDTO {
            /** @var \App\Domain\Models\User|null $user */
            $user = Auth::user();

            if (! $user) {
                throw new \Exception('Usuário não autenticado');
            }
            $data['company_id'] = $user->company_id;
            $tank = $this->tankRepository->create($data);

            return $this->mapToDTO($tank);
        });
    }

    public function showAllTanks(): AnonymousResourceCollection
    {
        $response = $this->tankRepository->paginate();

        return TankResource::collection($response->items())
            ->additional([
                'pagination' => [
                    'total'        => $response->total(),
                    'current_page' => $response->currentPage(),
                    'last_page'    => $response->lastPage(),
                    'first_page'   => $response->firstPage(),
                    'per_page'     => $response->perPage(),
                ],
            ]);
    }

    /**
     * Returns the details of a tank.
     */
    public function showTank(string $id): ?TankDTO
    {
        $tank = $this->tankRepository->showTank('id', $id);

        if (! $tank instanceof Tank) {
            return null;
        }

        return $this->mapToDTO($tank);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateTank(string $id, array $data): TankDTO
    {
        return DB::transaction(function () use ($id, $data): TankDTO {
            $tank = $this->tankRepository->update($id, $data);

            if (! $tank instanceof Tank) {
                throw new \Exception('Tank not found');
            }

            return $this->mapToDTO($tank);
        });
    }

    public function deleteTank(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->tankRepository->delete($id));
    }

    private function mapToDTO(?Tank $tank): ?TankDTO
    {
        if (! $tank instanceof Tank) {
            return null;
        }
        return new TankDTO(
            id: $tank->id,
            name: $tank->name,
            capacityLiters: $tank->capacity_liters,
            volume: $tank->volume,
            location: $tank->location,
            status: Status::from($tank->status),
            tankType: [
                'id'   => $tank->tankType->id ?? '',
                'name' => $tank->tankType->name ?? '',
            ],
            company: [
                'name' => $tank->company->name ?? '',
            ],
            createdAt: $tank->created_at?->toDateTimeString(),
            updatedAt: $tank->updated_at?->toDateTimeString()
        );
    }
}
