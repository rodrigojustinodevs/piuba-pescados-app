<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Traits;

use App\Infrastructure\Persistence\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait que aplica automaticamente o CompanyScope em qualquer Model de negócio.
 *
 * Uso:
 *   class Sale extends Model {
 *       use HasCompanyScope;
 *   }
 *
 * Garante que:
 *  - Todas as queries filtram por company_id automaticamente
 *  - Override explícito: Sale::withoutGlobalScope(CompanyScope::class)->get()
 *  - Novos registros recebem company_id do contexto automaticamente
 */
trait HasCompanyScope
{
    public static function bootHasCompanyScope(): void
    {
        // Aplica o scope em todas as queries
        static::addGlobalScope(new CompanyScope());

        // Preenche company_id automaticamente ao criar registros
        static::creating(function (self $model): void {
            $needsCompanyId = ! property_exists($model, 'company_id') || $model->company_id === null;

            if (
                $needsCompanyId &&
                app()->bound(\App\Domain\ValueObjects\TenantContext::class)
            ) {
                /** @var \App\Domain\ValueObjects\TenantContext $context */
                $context = app(\App\Domain\ValueObjects\TenantContext::class);

                if (! $context->isGlobal()) {
                    $model->company_id = $context->companyId;
                }
            }
        });
    }

    /** Shortcut para desativar o scope em uma query específica. */
    public static function allCompanies(): Builder
    {
        return static::withoutGlobalScope(CompanyScope::class);
    }
}
