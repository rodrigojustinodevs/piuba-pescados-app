<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum PositionEnum: string
{
    case ADMIN = 'admin';
    case GENERAL_MANAGER = 'general_manager';
    
    case PRODUCTION_MANAGER = 'production_manager';
    case FIELD_OPERATOR = 'field_operator';
    
    case SALES_MANAGER = 'sales_manager';
    case SALES_REP = 'sales_rep';
    
    case FINANCIAL_ANALYST = 'financial_analyst';
    case BILLING_CLERK = 'billing_clerk';
    
    case LOGISTICS_DISPATCHER = 'logistics_dispatcher';

    /**
     * Retorna o nome amigável para exibição no frontend (Next.js)
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN                => 'Administrador',
            self::GENERAL_MANAGER      => 'Gerente Geral',
            self::PRODUCTION_MANAGER   => 'Gerente de Produção',
            self::FIELD_OPERATOR       => 'Operador de Campo',
            self::SALES_MANAGER        => 'Gerente Comercial',
            self::SALES_REP            => 'Vendedor',
            self::FINANCIAL_ANALYST    => 'Analista Financeiro',
            self::BILLING_CLERK        => 'Faturista',
            self::LOGISTICS_DISPATCHER => 'Logística e Expedição',
        };
    }
}