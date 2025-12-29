<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Domain\Models\User;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Name;
use App\Domain\ValueObjects\UserId;

final class UserMapper
{
    /**
     * Converte Model para array usando Value Objects
     *
     * @return array<string, mixed>
     */
    public static function toArray(User $model): array
    {
        return [
            'id'                => $model->id,
            'name'              => $model->name,
            'email'             => $model->email,
            'is_admin'          => $model->is_admin ?? false,
            'email_verified_at' => $model->email_verified_at?->toDateTimeString(),
            'created_at'        => $model->created_at?->toDateTimeString(),
            'updated_at'        => $model->updated_at?->toDateTimeString(),
        ];
    }

    /**
     * Converte array de request para array de persistência
     * Encapsula criação de Value Objects e validações
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function fromRequest(array $data): array
    {
        $mapped = [];

        if (isset($data['name'])) {
            $name           = new Name($data['name']);
            $mapped['name'] = $name->value();
        }

        if (isset($data['email'])) {
            $email           = new Email($data['email']);
            $mapped['email'] = $email->value();
        }

        if (isset($data['password'])) {
            $mapped['password'] = $data['password'];
        }

        if (isset($data['is_admin'])) {
            $mapped['is_admin'] = (bool) $data['is_admin'];
        }

        return $mapped;
    }

    /**
     * Cria UserId Value Object a partir de string
     */
    public static function createUserId(string $id): UserId
    {
        return UserId::fromString($id);
    }

    /**
     * Valida e cria Email Value Object
     */
    public static function createEmail(string $email): Email
    {
        return new Email($email);
    }

    /**
     * Valida e cria Name Value Object
     */
    public static function createName(string $name): Name
    {
        return new Name($name);
    }
}
