<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\TankInputDTO;
use App\Domain\Models\Tank;
use App\Domain\Repositories\TankRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateTankUseCase
{
    public function __construct(
        private TankRepositoryInterface $tankRepository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): Tank
    {
        $tank = $this->tankRepository->findOrFail($id);

        $data['company_id'] = $this->companyResolver->resolve(
            $data['company_id'] ?? $data['companyId'] ?? (string) $tank->company_id,
        );

        $dto = TankInputDTO::fromArray($data);

        return DB::transaction(function () use ($id, $dto): Tank {
            $updated = $this->tankRepository->update($id, [
                'company_id'      => $dto->companyId,
                'tank_type_id'    => $dto->tankTypeId,
                'name'            => $dto->name,
                'capacity_liters' => $dto->capacityLiters,
                'location'        => $dto->location,
                'status'          => $dto->status,
            ]);

            return $updated->refresh();
        });
    }
}
