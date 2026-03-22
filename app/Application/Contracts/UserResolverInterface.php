<?php

declare(strict_types=1);

namespace App\Application\Contracts;

interface UserResolverInterface
{
    /**
     * Resolve o user_id ou lança exceção se não encontrado.
     *
     * Ordem de prioridade:
     *  1. $hint — valor explícito vindo do payload (ex: admin operando em nome de outro usuário)
     *  2. user_id direto no usuário autenticado
     *
     * @throws \App\Application\Exceptions\UserNotFoundException
     */
    public function resolve(?string $hint = null): string;

    /**
     * Igual ao resolve(), mas retorna null em vez de lançar exceção.
     * Útil para fluxos opcionais onde user_id pode estar ausente.
     */
    public function tryResolve(?string $hint = null): ?string;
}
