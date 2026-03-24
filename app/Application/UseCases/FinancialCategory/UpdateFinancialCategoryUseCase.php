<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialCategory;

use App\Domain\Enums\FinancialType;
use App\Domain\Models\FinancialCategory;
use App\Domain\Repositories\FinancialCategoryRepositoryInterface;

final readonly class UpdateFinancialCategoryUseCase
{
    public function __construct(
        private FinancialCategoryRepositoryInterface $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados já validados pelo FormRequest
     */
    public function execute(string $id, array $data): FinancialCategory
    {
        $attributes = array_filter([
            'name' => isset($data['name']) ? (string) $data['name'] : null,
            'type' => isset($data['type'])
                ? FinancialType::from((string) $data['type'])->value
                : null,
        ], static fn (?string $v): bool => $v !== null);

        return $this->repository->update($id, $attributes);
    }
}
