<?php

declare(strict_types=1);

namespace App\Application\Contracts;

interface CompanyResolverInterface
{
    /**
     * Resolve o company_id ou lança exceção se não encontrado.
     *
     * Ordem de prioridade:
     *  1. $hint — valor explícito vindo do payload (ex: admin operando em nome de outra empresa)
     *  2. company_id direto no usuário autenticado
     *  3. Primeira empresa vinculada ao usuário (relação N:N)
     *
     * @throws \App\Application\Exceptions\CompanyNotFoundException
     */
    public function resolve(?string $hint = null): string;

    /**
     * Igual ao resolve(), mas retorna null em vez de lançar exceção.
     * Útil para fluxos opcionais onde company_id pode estar ausente.
     */
    public function tryResolve(?string $hint = null): ?string;
}