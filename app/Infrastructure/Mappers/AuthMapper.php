<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\LoginInputDTO;
use App\Application\DTOs\UserContextDTO;
use App\Domain\Models\User;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\PlainPassword;

final class AuthMapper
{
    /**
     * Array validado do FormRequest → LoginInputDTO.
     *
     * @param array{email: string, password: string} $data
     */
    public function toLoginInput(array $data): LoginInputDTO
    {
        return new LoginInputDTO(
            email:    Email::of((string) $data['email']),
            password: PlainPassword::of((string) $data['password']),
        );
    }

    /**
     * Eloquent User → UserContextDTO.
     * Centraliza a lógica de quais campos do Model são expostos.
     */
    public function toUserContext(User $user): UserContextDTO
    {
        return UserContextDTO::fromModel($user);
    }
}
