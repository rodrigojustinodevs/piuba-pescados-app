<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Domain\Repositories\BiometryRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteBiometryUseCase
{
    public function __construct(
        private BiometryRepositoryInterface $biometryRepository,
    ) {
    }

    public function execute(string $id): void
    {
        DB::transaction(function () use ($id): void {
            $this->biometryRepository->findOrFail($id);
            $this->biometryRepository->delete($id);
        });
    }
}
