<?php

declare(strict_types=1);

namespace App\Presentation\Resources\User;

use App\Domain\Enums\UserStatusEnum;
use App\Domain\Models\Company;
use App\Domain\Models\CompanyUserPivot;
use App\Domain\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read string $email
 * @property-read string|null $phone
 * @property-read UserStatusEnum $status
 * @property-read \App\Domain\Enums\PositionEnum|null $position
 * @property-read \Illuminate\Support\Carbon|null $last_access_at
 * @property-read \Illuminate\Support\Carbon|null $email_verified_at
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $resource
 */
final class UserResource extends JsonResource
{
    /** Inclui a lista completa de empresas/roles do usuário (usado só no show, evita N+1 no index). */
    public bool $includeCompanies = false;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        $membership = $this->loadedMembership($this->resource);
        $company    = $this->loadedCompany($membership);

        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'emailVerifiedAt' => $this->email_verified_at?->toDateTimeString(),
            'status'          => $this->status->value,
            'position'        => $this->position?->value,
            'lastAccessAt'    => $this->last_access_at?->toDateTimeString(),
            'role'            => $membership?->role,
            'company'         => $company instanceof \App\Domain\Models\Company ? [
                'name' => $company->name,
                ...($request->user()?->isMasterAdmin() ? ['id' => $company->id] : []),
            ] : null,
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }

    /**
     * Reads whatever company_user membership the repository/use case already
     * eager-loaded (scoped to the relevant company_id) — this resource never
     * queries the database itself.
     */
    private function loadedMembership(User $user): ?CompanyUserPivot
    {
        if (! $user->relationLoaded('companyMemberships')) {
            return null;
        }

        return $user->companyMemberships->first();
    }

    private function loadedCompany(?CompanyUserPivot $membership): ?Company
    {
        if (!$membership instanceof \App\Domain\Models\CompanyUserPivot || ! $membership->relationLoaded('company')) {
            return null;
        }

        return $membership->company;
    }
}
