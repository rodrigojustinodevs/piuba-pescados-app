<?php

declare(strict_types=1);

namespace App\Application\Actions\Batch;

use App\Application\DTOs\BatchDistributionInputDTO;
use App\Application\DTOs\BatchInputDTO;
use App\Domain\Repositories\BatchRepositoryInterface;

final readonly class CreateSingleDistributedBatchAction
{
    public function __construct(
        private BatchRepositoryInterface $repository,
        private CalculateProportionalCostAction $calculateCost,
        private GenerateBatchNameAction $generateName,
    ) {}

    public function execute(
        BatchDistributionInputDTO $input,
        array $item,
        string $parentGroupId,
        int $totalQuantity
    ): \App\Domain\Models\Batch {
        $proportionalCost = $this->calculateCost->execute(
            $item['quantity'],
            $totalQuantity,
            $input->totalCost
        );

        $unitCost = $item['quantity'] > 0
            ? $proportionalCost / $item['quantity']
            : 0.0;

        $batchDto = new BatchInputDTO(
            name: $this->generateName->execute($input->species, $item['quantity']),
            description: $input->notes,
            species: $input->species,
            initialQuantity: $item['quantity'],
            entryDate: $input->entryDate,
            tankId: $item['tankId'],
            status: 'active',
            cultivation: $input->cultivation,
            parentGroupId: $parentGroupId,
            unitCost: $unitCost,
            totalCost: $proportionalCost,
        );

        return $this->repository->create($batchDto);
    }
}