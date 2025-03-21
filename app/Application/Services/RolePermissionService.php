<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Repositories\CompanyRepositoryInterface;
use App\Domain\Repositories\PermissionRepositoryInterface;
use App\Domain\Repositories\RoleRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;

class RolePermissionService
{

    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected CompanyRepositoryInterface $companyRepository,
        protected RoleRepositoryInterface $roleRepository,
        protected PermissionRepositoryInterface $permissionRepository
    ) {
    }

    public function assignRoleToUser(array $data): void
    {
        $user = $this->userRepository->showUser('id', $data['user_id']);
        $role = $this->roleRepository->showRole('name', $data['role_name']);
        $user->roles()->syncWithoutDetaching($role);
    }

    public function assignPermissionToUser(array $data): void
    {
        $user = $this->userRepository->showUser('id', $data['user_id']);
        $permission = $this->permissionRepository->showPermission('name', $data['permission_name']);
        $user->permissions()->syncWithoutDetaching($permission);
    }

    public function assignRoleToUserInCompany(array $data): void
    {
        $user = $this->userRepository->showUser('id', $data['user_id']);
        $role = $this->roleRepository->showRole('name', $data['role_name']);
        $company = $this->companyRepository->showCompany('id', $data['company_id']);
        $user->companyRoles($company)->syncWithoutDetaching($role);
    }

    public function assignPermissionToUserInCompany(array $data): void
    {
        $user = $this->userRepository->showUser('id', $data['user_id']);
        $permission = $this->permissionRepository->showPermission('name', $data['permission_name']);
        $company = $this->companyRepository->showCompany('id', $data['company_id']);
        $user->companyPermissions($company)->syncWithoutDetaching($permission);
    }
}
