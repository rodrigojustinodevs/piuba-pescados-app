<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Domain\Repositories\BiometryRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteBiometryUseCase
{
    public function __construct(
        protected BiometryRepositoryInterface $biometryRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->biometryRepository->delete($id));
    }
}
