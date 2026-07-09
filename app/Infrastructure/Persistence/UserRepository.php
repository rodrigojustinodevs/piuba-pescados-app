<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\UserInputDTO;
use App\Domain\Models\User;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;
use Illuminate\Pagination\LengthAwarePaginator;

final class UserRepository implements UserRepositoryInterface
{
    /** @var list<string> */
    private const array SORTABLE_COLUMNS = ['name', 'email', 'created_at'];

    private const array DEFAULT_RELATIONS = [
        'roles:id,name',
        'companyMemberships.company',
    ];

    public function create(UserInputDTO $dto): User
    {
        /** @var User $user */
        $user = User::create($dto->toPersistence());

        return $user;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): User
    {
        $user = $this->findOrFail($id);

        if ($attributes !== []) {
            $user->update($attributes);
            $user->refresh();
        }

        return $user;
    }

    /**
     * Eager-loads the company_user membership (and its related company) for the
     * active company context, so UserResource never has to query the database
     * itself — it only reads whatever this repository already fetched.
     */
    public function findOrFail(string $id): User
    {
        $companyId = CompanyContext::getCompanyId();

        return User::query()
            ->when(
                is_string($companyId) && $companyId !== '',
                fn ($q) => $q->with([
                    'companyMemberships' => fn ($mq) => $mq->where('company_id', $companyId)->with('company'),
                ]),
            )
            ->findOrFail($id);
    }

    /**
     * @param array{
     *     companyId?: string|null,
     *     search?: string|null,
     *     role?: string|null,
     *     isActive?: bool|null,
     *     sortBy?: string|null,
     *     sortDir?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface
    {
        $companyId = $filters['companyId'] ?? null;
        $search    = $filters['search'] ?? null;
        $role      = $filters['role'] ?? null;
        $isActive  = $filters['isActive'] ?? null;
        $perPage   = (int) ($filters['perPage'] ?? 25);

        $sortBy = in_array($filters['sortBy'] ?? null, self::SORTABLE_COLUMNS, true)
            ? $filters['sortBy']
            : 'created_at';
        $sortDir = strtolower((string) ($filters['sortDir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = User::query()
            ->with(self::DEFAULT_RELATIONS)
            ->when(
                is_string($companyId) && $companyId !== '',
                function ($q) use ($companyId, $role, $isActive): void {
                    $q->whereHas('companyMemberships', function ($mq) use ($companyId, $role, $isActive): void {
                        $mq->where('company_id', $companyId);

                        if ($role !== null) {
                            $mq->where('role', $role);
                        }

                        if ($isActive !== null) {
                            $mq->where('is_active', $isActive);
                        }
                    })->with([
                        'companyMemberships' => fn ($mq) => $mq->where('company_id', $companyId)->with('company'),
                    ]);
                },
            )
            ->when(
                is_string($search) && $search !== '',
                static fn ($q) => $q->whereAny(['name', 'email'], 'like', '%' . $search . '%'),
            )
            ->orderBy($sortBy, $sortDir);

        /** @var LengthAwarePaginator<int, User> $paginator */
        $paginator = $query->paginate($perPage);

        return new PaginationPresentr($paginator);
    }

    public function detachFromCompany(string $userId, string $companyId): void
    {
        $this->findOrFail($userId)->companyMemberships()->where('company_id', $companyId)->delete();
    }

    public function updateCompanyStatus(string $userId, string $companyId, bool $isActive): void
    {
        $this->findOrFail($userId)
            ->companyMemberships()
            ->where('company_id', $companyId)
            ->update(['is_active' => $isActive]);
    }
}
