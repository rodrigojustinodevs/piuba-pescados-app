<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Scopes;

use App\Domain\ValueObjects\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global Scope Eloquent que filtra automaticamente por company_id.
 *
 * Aplicado automaticamente em todos os models que usam HasCompanyScope.
 * Bypass via ->withoutGlobalScope(CompanyScope::class) apenas em contextos admin.
 */
final class CompanyScope implements Scope
{
    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $builder
     * @param  TModel  $model
     */
    public function apply(Builder $builder, Model $model): void
    {
        /** @var TenantContext|null $context */
        $context = app()->bound(TenantContext::class)
            ? app(TenantContext::class)
            : null;

        if ($context === null) {
            // Fora do contexto de request autenticado (ex: console/jobs)
            // Não aplica filtro — responsabilidade do dev garantir isolamento nesses casos
            return;
        }

        if ($context->isGlobal()) {
            // master_admin: sem filtro de tenant
            return;
        }

        $builder->where($model->getTable() . '.company_id', (string) $context->companyId);
    }
}
