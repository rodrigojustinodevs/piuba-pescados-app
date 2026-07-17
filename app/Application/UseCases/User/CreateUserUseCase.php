<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\UserInputDTO;
use App\Application\UseCases\Auth\AssignUserToCompanyUseCase;
use App\Domain\Enums\RolesEnum;
use App\Domain\Models\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class CreateUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AssignUserToCompanyUseCase $assignUserToCompanyUseCase,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data, User $actingUser): User
    {
        $companyId = CompanyContext::resolveTargetCompanyId(
            isset($data['companyId']) ? (string) $data['companyId'] : null,
        );
        $role = RolesEnum::from((string) $data['role']);

        // O cadastro feito pelo admin nunca captura senha do cliente: uma senha
        // aleatória é gerada aqui (hasheada automaticamente pelo cast do model).
        $data['password'] = Str::password(20);
        $dto              = UserInputDTO::fromArray($data);

        return DB::transaction(function () use ($dto, $companyId, $role, $actingUser): User {
            $user = $this->userRepository->create($dto);

            $this->assignUserToCompanyUseCase->execute($user->id, $companyId, $role, $actingUser);

            // Eager-load the just-created pivot so UserResource can read role/status
            // without querying the database itself.
            return $user->refresh()->load(['companies' => fn ($q) => $q->where('companies.id', $companyId)]);
        });
    }
}
