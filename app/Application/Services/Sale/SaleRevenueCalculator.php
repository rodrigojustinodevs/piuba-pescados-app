<?php

declare(strict_types=1);

namespace App\Application\Services\Sale;

use App\Domain\Models\Sale;
use App\Domain\ValueObjects\SaleAttributes;

/**
 * Application Service: cálculo de receita total de uma venda.
 *
 * Regra: total_revenue = total_weight × price_per_kg (arredondado a 2 casas).
 *
 * Centraliza a fórmula que estava duplicada em UpdateSaleUseCase e UpdateSaleAction.
 * Sem dependências externas — é puro PHP.
 *
 * Namespace: Application\Services (padrão do projeto).
 */
final class SaleRevenueCalculator
{
    private const int PRECISION = 2;

    /**
     * Calcula a receita considerando o payload do update e o estado atual da venda.
     * Campos ausentes em $attributes são complementados pelo estado atual de $sale.
     */
    public function calculate(Sale $sale, SaleAttributes $attributes): float
    {
        $weight = $attributes->resolveWeight($sale);
        $price  = $attributes->resolvePrice($sale);

        return round($weight * $price, self::PRECISION);
    }

    /**
     * Calcula direto de peso e preço explícitos.
     */
    public function calculateFrom(float $weight, float $pricePerKg): float
    {
        return round($weight * $pricePerKg, self::PRECISION);
    }
}
