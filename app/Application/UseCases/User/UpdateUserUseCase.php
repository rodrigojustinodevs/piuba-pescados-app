<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\UserInputDTO;
use App\Domain\Models\User;
use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): User
    {
        $user = $this->userRepository->findOrFail($id);
        $dto  = UserInputDTO::fromArray($data);

        return DB::transaction(fn (): User => $this->userRepository->update($user->id, $dto->toPersistence()));
    }
}
