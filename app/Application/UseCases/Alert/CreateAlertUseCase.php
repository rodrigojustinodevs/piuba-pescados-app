<?php

declare(strict_types=1);

namespace App\Application\UseCases\Alert;

use App\Application\DTOs\AlertDTO;
use App\Domain\Repositories\AlertRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateAlertUseCase
{
    public function __construct(protected AlertRepositoryInterface $repository)
    {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): AlertDTO
    {
        return DB::transaction(function () use ($data): AlertDTO {
            $alert                 = $this->repository->create($data);
            $alertArray            = $alert->toArray();
            $alertArray['company'] = $alert->company ?? null;

            return AlertDTO::fromArray($alertArray);
        });
    }
}
