<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\BiometryDTO;
use App\Application\UseCases\Biometry\CreateBiometryUseCase;
use App\Application\UseCases\Biometry\DeleteBiometryUseCase;
use App\Application\UseCases\Biometry\ListBiometriesUseCase;
use App\Application\UseCases\Biometry\ShowBiometryUseCase;
use App\Application\UseCases\Biometry\UpdateBiometryUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BiometryService
{
    public function __construct(
        protected CreateBiometryUseCase $createBiometryUseCase,
        protected ListBiometriesUseCase $listBiometriesUseCase,
        protected ShowBiometryUseCase $showBiometryUseCase,
        protected UpdateBiometryUseCase $updateBiometryUseCase,
        protected DeleteBiometryUseCase $deleteBiometryUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): BiometryDTO
    {
        return $this->createBiometryUseCase->execute($data);
    }

    public function showAllBiometries(): AnonymousResourceCollection
    {
        return $this->listBiometriesUseCase->execute();
    }

    public function showBiometry(string $id): ?BiometryDTO
    {
        return $this->showBiometryUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateBiometry(string $id, array $data): BiometryDTO
    {
        return $this->updateBiometryUseCase->execute($id, $data);
    }

    public function deleteBiometry(string $id): bool
    {
        return $this->deleteBiometryUseCase->execute($id);
    }
}
