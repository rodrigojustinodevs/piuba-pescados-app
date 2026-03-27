<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

interface TankTypeRepositoryInterface
{
    /**
     * Lista tipos de tanque ordenados por nome (somente leitura).
     *
     * @return list<array{id: string, name: string|null, description: string|null}>
     */
    public function listAllOrdered(): array;
}
