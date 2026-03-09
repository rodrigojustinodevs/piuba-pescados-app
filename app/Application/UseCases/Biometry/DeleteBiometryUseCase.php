<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Domain\Models\Biometry;
use App\Domain\Repositories\BiometryRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DeleteBiometryUseCase
{
    public function __construct(
        private readonly BiometryRepositoryInterface $biometryRepository,
    ) {}

    public function execute(string $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $biometry = $this->biometryRepository->showBiometry('id', $id);
            if (! $biometry instanceof Biometry) {
                throw new RuntimeException('Biometry not found');
            }
            return $this->biometryRepository->delete($id);
        });
    }
}
