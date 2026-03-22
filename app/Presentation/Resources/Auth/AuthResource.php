<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Auth;

use App\Application\DTOs\LoginOutputDTO;
use Illuminate\Http\Request;

/**
 * Does not extend JsonResource because the input data is a DTO,
 * not an Eloquent Model. Converts the DTO to an array of response.
 */
final class AuthResource
{
    public function __construct(
        private readonly LoginOutputDTO $dto,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'token'     => $this->dto->token,
            'tokenType' => $this->dto->tokenType,
            'expiresIn' => $this->dto->expiresIn,
        ];
    }
}