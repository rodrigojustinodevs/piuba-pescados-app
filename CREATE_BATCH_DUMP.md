- `app/Presentation/Controllers/BatchController.php`
- `app/Presentation/Requests/Batch/BatchStoreRequest.php`
- `app/Presentation/Resources/Batch/BatchResource.php`
- `app/Application/UseCases/Batch/CreateBatchUseCase.php`
- `app/Application/Actions/Batch/ValidateActiveBatchInTankAction.php`
- `app/Application/DTOs/BatchInputDTO.php`
- `app/Application/Contracts/CompanyResolverInterface.php`
- `app/Application/Services/CompanyResolver.php`
- `app/Domain/Repositories/BatchRepositoryInterface.php`
- `app/Infrastructure/Persistence/BatchRepository.php`
- `app/Domain/Models/Batch.php`
- `app/Domain/Enums/BatchStatus.php`
- `app/Domain/ValueObjects/EntryDate.php`
- `app/Domain/ValueObjects/InitialQuantity.php`
- `app/Domain/ValueObjects/Species.php`
- `app/Domain/Exceptions/TankAlreadyHasActiveBatchException.php`
- `database/migrations/2025_03_10_234704_create_batches_table.php`

---

## routes/app/company/batch.php

```php
<?php

declare(strict_types=1);

use App\Presentation\Controllers\BatchController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-batch|view-batch|update-batch|delete-batch'])
    ->group(function (): void {
        Route::post('batch', [BatchController::class, 'store']);
        Route::get('batches', [BatchController::class, 'index']);
        Route::get('batch/{id}', [BatchController::class, 'show']);
        Route::put('batch/{id}', [BatchController::class, 'update']);
        Route::delete('batch/{id}', [BatchController::class, 'destroy']);
        Route::post('batch/{id}/finish', [BatchController::class, 'finish']);
    });
```

## app/Presentation/Controllers/BatchController.php

```php
<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Batch\CreateBatchUseCase;
use App\Application\UseCases\Batch\DeleteBatchUseCase;
use App\Application\UseCases\Batch\FinishBatchUseCase;
use App\Application\UseCases\Batch\ListBatchesUseCase;
use App\Application\UseCases\Batch\ShowBatchUseCase;
use App\Application\UseCases\Batch\UpdateBatchUseCase;
use App\Presentation\Requests\Batch\BatchFinishRequest;
use App\Presentation\Requests\Batch\BatchStoreRequest;
use App\Presentation\Requests\Batch\BatchUpdateRequest;
use App\Presentation\Resources\Batch\BatchResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Batches", description="Lotes")
 * @OA\Schema(
 *     schema="Batch",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="entryDate", type="string", format="date", example="2025-01-15"),
 *     @OA\Property(property="initialQuantity", type="integer", minimum=1),
 *     @OA\Property(property="species", type="string"),
 *     @OA\Property(property="status", type="string", enum={"active","finished"}),
 *     @OA\Property(property="cultivation", type="string", enum={"growout","nursery"}, nullable=true),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
final class BatchController
{
    /**
     * @OA\Get(
     *     path="/company/batches",
     *     summary="List batches",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active","finished"})
     *     ),
     *     @OA\Parameter(name="tank_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="species", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of batches",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Batch"))
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="first_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(
        Request $request,
        ListBatchesUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only(['status', 'tank_id', 'species', 'per_page', 'page']),
        );

        return ApiResponse::success(
            data:       BatchResource::collection($paginator->items()),
            pagination: [
                'total'        => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'first_page'   => $paginator->firstPage(),
                'per_page'     => $paginator->perPage(),
            ],
        );
    }

    /**
     * @OA\Get(
     *     path="/company/batch/{id}",
     *     summary="Get batch by ID",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Batch found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Batch")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Batch not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(
        string $id,
        ShowBatchUseCase $useCase,
    ): JsonResponse {
        $batch = $useCase->execute($id);

        return ApiResponse::success(
            data: new BatchResource($batch),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/batch",
     *     summary="Create a batch",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tankId","name","entryDate","initialQuantity","species","cultivation"},
     *             @OA\Property(property="tankId", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="entryDate", type="string", format="date", example="2025-01-15"),
     *             @OA\Property(property="initialQuantity", type="integer", minimum=1),
     *             @OA\Property(property="species", type="string", maxLength=255),
     *             @OA\Property(property="cultivation", type="string", enum={"growout","nursery"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="Batch created"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(
        BatchStoreRequest $request,
        CreateBatchUseCase $useCase,
    ): JsonResponse {
        $batch = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new BatchResource($batch),
            message: 'Batch created successfully.',
        );
    }

    /**
     * @OA\Put(
     *     path="/company/batch/{id}",
     *     summary="Update a batch",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="tankId", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="entryDate", type="string", format="date"),
     *             @OA\Property(property="initialQuantity", type="integer", minimum=1),
     *             @OA\Property(property="species", type="string", maxLength=255),
     *             @OA\Property(property="status", type="string", enum={"active","finished"}),
     *             @OA\Property(property="cultivation", type="string", enum={"growout","nursery"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Batch updated"),
     *     @OA\Response(response=404, description="Batch not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(
        BatchUpdateRequest $request,
        string $id,
        UpdateBatchUseCase $useCase,
    ): JsonResponse {
        $batch = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    new BatchResource($batch),
            message: 'Batch updated successfully.',
        );
    }

    /**
     * @OA\Delete(
     *     path="/company/batch/{id}",
     *     summary="Delete a batch",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Batch deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response", nullable=true),
     *             @OA\Property(property="message", type="string", example="Batch deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Batch not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(
        string $id,
        DeleteBatchUseCase $useCase,
    ): JsonResponse {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Batch deleted successfully.');
    }

    /**
     * @OA\Post(
     *     path="/company/batch/{id}/finish",
     *     summary="Finish a batch (harvest)",
     *     description="Finishes the batch harvest and returns a biological and financial report.",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"total_weight","price_per_kg"},
     *             @OA\Property(property="total_weight", type="number", format="float", minimum=0, example=1250.5),
     *             @OA\Property(property="price_per_kg", type="number", format="float", minimum=0, example=12.00),
     *             @OA\Property(
     *                 property="harvest_date",
     *                 type="string",
     *                 format="date",
     *                 nullable=true,
     *                 example="2025-03-10"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Batch finished with performance report"),
     *     @OA\Response(response=404, description="Batch not found"),
     *     @OA\Response(response=422, description="Batch already finished or validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function finish(
        BatchFinishRequest $request,
        string $id,
        FinishBatchUseCase $useCase,
    ): JsonResponse {
        $report = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    $report,
            message: 'Batch finished successfully.',
        );
    }
}
```

## app/Presentation/Requests/Batch/BatchStoreRequest.php

```php
<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Batch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'tankId'          => ['required', 'uuid', 'exists:tanks,id'],
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'entryDate'       => ['required', 'date'],
            'initialQuantity' => ['required', 'integer', 'min:1'],
            'species'         => ['required', 'string', 'max:255'],
            'cultivation'     => ['required', Rule::in(['growout', 'nursery'])->__toString()],
        ];
    }

    /** @return array<string, string> */
    #[\Override]
    public function messages(): array
    {
        return [
            'tankId.required'          => 'The tank ID is required.',
            'tankId.uuid'              => 'The tank ID must be a valid UUID.',
            'tankId.exists'            => 'The tank ID must exist in the tanks table.',
            'name.required'            => 'The name is required.',
            'name.string'              => 'The name must be a string.',
            'name.max'                 => 'The name must be less than 255 characters.',
            'description.string'       => 'The description must be a string.',
            'description.max'          => 'The description must be less than 255 characters.',
            'entryDate.required'       => 'The entry date is required.',
            'entryDate.date'           => 'The entry date must be a valid date.',
            'initialQuantity.required' => 'The initial quantity is required.',
            'initialQuantity.integer'  => 'The initial quantity must be an integer.',
            'initialQuantity.min'      => 'The initial quantity must be at least 1.',
            'species.required'         => 'The species is required.',
            'species.string'           => 'The species must be a string.',
            'species.max'              => 'The species must be less than 255 characters.',
            'cultivation.required'     => 'The cultivation is required.',
            'cultivation.in'           => 'The cultivation must be either growout or nursery.',
        ];
    }
}
```

## app/Presentation/Resources/Batch/BatchResource.php

```php
<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Batch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string                          $id
 * @property-read string|null                     $name
 * @property-read string|null                     $description
 * @property-read \Illuminate\Support\Carbon|null $entry_date
 * @property-read int                             $initial_quantity
 * @property-read string                          $species
 * @property-read string                          $status
 * @property-read string|null                     $cultivation
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Tank|null    $tank
 */
final class BatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'description'     => $this->description,
            'entryDate'       => $this->entry_date?->toDateString(),
            'initialQuantity' => $this->initial_quantity,
            'species'         => $this->species,
            'status'          => $this->status,
            'cultivation'     => $this->cultivation,
            'createdAt'       => $this->created_at?->toDateTimeString(),
            'updatedAt'       => $this->updated_at?->toDateTimeString(),

            'tank' => $this->whenLoaded('tank', fn (): array => [
                'id'   => $this->tank->id,
                'name' => $this->tank->name,
            ]),
        ];
    }
}
```

## app/Application/UseCases/Batch/CreateBatchUseCase.php

```php
<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Application\Actions\Batch\ValidateActiveBatchInTankAction;
use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\BatchInputDTO;
use App\Domain\Enums\BatchStatus;
use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateBatchUseCase
{
    public function __construct(
        private BatchRepositoryInterface $repository,
        private ValidateActiveBatchInTankAction $validateTank,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $data Validated data from the FormRequest
     */
    public function execute(array $data): Batch
    {
        $dto       = BatchInputDTO::fromArray($data);
        $companyId = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? $data['companyId'] ?? null,
        );

        if ($dto->status === BatchStatus::ACTIVE->value) {
            $this->validateTank->execute($dto->tankId, $companyId);
        }

        return DB::transaction(fn (): Batch => $this->repository->create($dto));
    }
}
```

## app/Application/Actions/Batch/ValidateActiveBatchInTankAction.php

```php
<?php

declare(strict_types=1);

namespace App\Application\Actions\Batch;

use App\Domain\Exceptions\TankAlreadyHasActiveBatchException;
use App\Domain\Repositories\BatchRepositoryInterface;

final readonly class ValidateActiveBatchInTankAction
{
    public function __construct(
        private BatchRepositoryInterface $batchRepository,
    ) {
    }

    public function execute(string $tankId, ?string $exceptBatchId = null): void
    {
        if ($this->batchRepository->hasActiveBatchInTank($tankId, $exceptBatchId)) {
            throw new TankAlreadyHasActiveBatchException($tankId);
        }
    }
}
```

## app/Application/DTOs/BatchInputDTO.php

```php
<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\ValueObjects\EntryDate;
use App\Domain\ValueObjects\InitialQuantity;
use App\Domain\ValueObjects\Species;

final readonly class BatchInputDTO
{
    public function __construct(
        public ?string $name,
        public ?string $description,
        public ?string $species,
        public ?int $initialQuantity,
        public ?string $entryDate,
        public ?string $tankId,
        public string $status = 'active',
        public ?string $cultivation = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $species         = isset($data['species']) ? (new Species($data['species']))->value() : null;
        $rawQuantity     = $data['initialQuantity'] ?? $data['initial_quantity'] ?? null;
        $initialQuantity = $rawQuantity !== null
            ? InitialQuantity::fromInt((int) $rawQuantity)->value()
            : null;
        $rawEntryDate = $data['entryDate'] ?? $data['entry_date'] ?? null;
        $entryDate    = $rawEntryDate !== null
            ? EntryDate::fromString((string) $rawEntryDate)->toDateString()
            : null;

        return new self(
            name:            $data['name'] ?? null,
            description:     $data['description'] ?? null,
            species:         $species,
            initialQuantity: $initialQuantity,
            entryDate:       $entryDate,
            tankId:          (string) ($data['tank_id'] ?? $data['tankId'] ?? ''),
            status:          $data['status'] ?? 'active',
            cultivation:     $data['cultivation'] ?? null,
        );
    }

    /** @return array<string, mixed> */
    public function toPersistence(): array
    {
        return array_filter([
            'name'             => $this->name,
            'description'      => $this->description,
            'species'          => $this->species,
            'initial_quantity' => $this->initialQuantity,
            'entry_date'       => $this->entryDate,
            'tank_id'          => $this->tankId,
            'status'           => $this->status,
            'cultivation'      => $this->cultivation,
        ], static fn (int | string | null $v): bool => $v !== null);
    }
}
```

## app/Application/Contracts/CompanyResolverInterface.php

```php
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
```

## app/Application/Services/CompanyResolver.php

```php
<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\Exceptions\CompanyNotFoundException;
use App\Domain\Models\User;
use Illuminate\Contracts\Auth\Guard;

final readonly class CompanyResolver implements CompanyResolverInterface
{
    public function __construct(
        private Guard $auth,
    ) {
    }

    public function resolve(?string $hint = null): string
    {
        $companyId = $this->tryResolve($hint);

        if ($companyId === null) {
            throw $hint !== null
                ? CompanyNotFoundException::forHint($hint)
                : new CompanyNotFoundException();
        }

        return $companyId;
    }

    public function tryResolve(?string $hint = null): ?string
    {
        // 1. Hint explícito — admin operando em outra empresa, multi-tenant, etc.
        if ($this->isValidId($hint)) {
            return $hint;
        }

        $user = $this->authenticatedUser();

        if (! $user instanceof User) {
            return null;
        }

        // 2. company_id diretamente no usuário (relação simples 1:N)
        $directId = $this->resolveDirectCompanyId($user);

        if ($this->isValidId($directId)) {
            return $directId;
        }

        // 3. Primeira empresa vinculada (relação N:N via pivot)
        return $this->resolveFromRelation($user);
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private function authenticatedUser(): ?User
    {
        $user = $this->auth->user();

        return $user instanceof User ? $user : null;
    }

    private function resolveDirectCompanyId(User $user): ?string
    {
        $attributes = $user->getAttributes();
        $id         = $attributes['company_id'] ?? null;

        return is_string($id) && $id !== '' ? $id : null;
    }

    private function resolveFromRelation(User $user): ?string
    {
        // User has companies() relation - always present

        $id = $user->companies()->value('companies.id');

        return is_string($id) && $id !== '' ? $id : null;
    }

    private function isValidId(mixed $value): bool
    {
        return is_string($value) && $value !== '';
    }
}
```

## app/Domain/Repositories/BatchRepositoryInterface.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\BatchInputDTO;
use App\Domain\Models\Batch;

interface BatchRepositoryInterface
{
    /**
     * @param array{
     *     status?: string|null,
     *     tank_id?: string|null,
     *     species?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface;

    public function findOrFail(string $id): Batch;

    /**
     * Find a batch by a specific field.
     * Kept for backward-compatibility with external modules (Feeding, Biometry, Transfer, etc.).
     */
    public function showBatch(string $field, string | int $value): ?Batch;

    public function create(BatchInputDTO $dto): Batch;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Batch;

    public function delete(string $id): bool;

    /**
     * Check if there is another active batch in the tank.
     */
    public function hasActiveBatchInTank(string $tankId, ?string $exceptBatchId = null): bool;
}
```

## app/Infrastructure/Persistence/BatchRepository.php

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\BatchInputDTO;
use App\Domain\Enums\BatchStatus;
use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final class BatchRepository implements BatchRepositoryInterface
{
    private const array DEFAULT_RELATIONS = [
        'tank:id,name,company_id,capacity_liters',
    ];

    /**
     * @param array{
     *     status?: string|null,
     *     tank_id?: string|null,
     *     species?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface
    {
        $paginator = Batch::with(self::DEFAULT_RELATIONS)
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where('status', BatchStatus::from($filters['status'])->value),
            )
            ->when(
                ! empty($filters['tank_id']),
                static fn ($q) => $q->where('tank_id', $filters['tank_id']),
            )
            ->when(
                ! empty($filters['species']),
                static fn ($q) => $q->where('species', 'like', '%' . $filters['species'] . '%'),
            )
            ->latest('entry_date')
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function findOrFail(string $id): Batch
    {
        return Batch::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }

    public function showBatch(string $field, string | int $value): ?Batch
    {
        return Batch::with(self::DEFAULT_RELATIONS)
            ->where($field, $value)
            ->first();
    }

    public function create(BatchInputDTO $dto): Batch
    {
        /** @var Batch $batch */
        $batch = Batch::create($dto->toPersistence());

        return $batch->load(self::DEFAULT_RELATIONS);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Batch
    {
        $batch = $this->findOrFail($id);
        $batch->update($attributes);

        return $batch->refresh();
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function hasActiveBatchInTank(string $tankId, ?string $exceptBatchId = null): bool
    {
        return Batch::query()
            ->where('tank_id', $tankId)
            ->where('status', BatchStatus::ACTIVE->value)
            ->when($exceptBatchId, static function ($query, string $id): void {
                $query->where('id', '!=', $id);
            })
            ->exists();
    }
}
```

## app/Domain/Models/Batch.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\BatchStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string              $id
 * @property string|null         $name
 * @property string|null         $description
 * @property string              $tank_id
 * @property \Carbon\Carbon|null $entry_date
 * @property int                 $initial_quantity
 * @property float               $unit_cost
 * @property string              $species
 * @property string              $status
 * @property string|null         $cultivation
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read Tank|null $tank
 */
class Batch extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'description',
        'tank_id',
        'entry_date',
        'initial_quantity',
        'unit_cost',
        'species',
        'status',
        'cultivation',
    ];

    protected $casts = [
        'entry_date'       => 'date:Y-m-d',
        'initial_quantity' => 'integer',
        'unit_cost'        => 'decimal:2',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (Batch $batch): void {
            $batch->id ??= (string) Str::uuid();
            $batch->status ??= BatchStatus::ACTIVE->value;
        });
    }

    // -------------------------------------------------------------------------
    // Relacionamentos
    // -------------------------------------------------------------------------

    /**
     * @phpstan-return BelongsTo<Tank, static>
     */
    public function tank(): BelongsTo
    {
        /** @var BelongsTo<Tank, static> $relation */
        $relation = $this->belongsTo(Tank::class, 'tank_id');

        return $relation;
    }

    // -------------------------------------------------------------------------
    // Helpers de domínio (leitura apenas)
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === BatchStatus::ACTIVE->value;
    }

    public function isFinished(): bool
    {
        return $this->status === BatchStatus::FINISHED->value;
    }

    public function currentStatus(): BatchStatus
    {
        return BatchStatus::from($this->status);
    }
}
```

## app/Domain/Enums/BatchStatus.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum BatchStatus: string
{
    case ACTIVE   = 'active';
    case FINISHED = 'finished';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isFinished(): bool
    {
        return $this === self::FINISHED;
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE   => 'Active',
            self::FINISHED => 'Finished',
        };
    }
}
```

## app/Domain/ValueObjects/EntryDate.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class EntryDate
{
    public function __construct(
        private CarbonImmutable $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $now     = CarbonImmutable::now();
        $minDate = CarbonImmutable::create(1900, 1, 1);

        if ($this->value->isBefore($minDate)) {
            throw new InvalidArgumentException('Entry date cannot be before 1900-01-01.');
        }

        if ($this->value->isAfter($now->addYear())) {
            throw new InvalidArgumentException('Entry date cannot be more than 1 year in the future.');
        }
    }

    public function value(): CarbonImmutable
    {
        return $this->value;
    }

    public function toDateString(): string
    {
        return $this->value->toDateString();
    }

    public function toDateTimeString(): string
    {
        return $this->value->toDateTimeString();
    }

    public function equals(self $other): bool
    {
        return $this->value->equalTo($other->value);
    }

    public function toString(): string
    {
        return $this->value->toDateString();
    }

    public static function fromString(string $value): self
    {
        try {
            $date = CarbonImmutable::parse($value);
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Invalid date format: {$value}", 0, $e);
        }

        return new self($date);
    }

    public static function fromCarbon(CarbonImmutable $value): self
    {
        return new self($value);
    }
}
```

## app/Domain/ValueObjects/InitialQuantity.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class InitialQuantity
{
    public function __construct(
        private int $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->value < 1) {
            throw new InvalidArgumentException('Initial quantity must be at least 1.');
        }

        if ($this->value > 10_000_000) {
            throw new InvalidArgumentException('Initial quantity cannot exceed 10,000,000.');
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toString(): string
    {
        return (string) $this->value;
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }
}
```

## app/Domain/ValueObjects/Species.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Species
{
    public function __construct(
        private string $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (in_array(trim($this->value), ['', '0'], true)) {
            throw new InvalidArgumentException('Species cannot be empty.');
        }

        if (mb_strlen(trim($this->value)) < 2) {
            throw new InvalidArgumentException('Species must have at least 2 characters.');
        }

        if (mb_strlen(trim($this->value)) > 255) {
            throw new InvalidArgumentException('Species must not exceed 255 characters.');
        }
    }

    public function value(): string
    {
        return trim($this->value);
    }

    public function equals(self $other): bool
    {
        return $this->value() === $other->value();
    }

    public function toString(): string
    {
        return $this->value();
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
```

## app/Domain/Exceptions/TankAlreadyHasActiveBatchException.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class TankAlreadyHasActiveBatchException extends RuntimeException
{
    public function __construct(string $tankId)
    {
        parent::__construct("Tank [{$tankId}] already has an active batch.");
    }
}
```

## database/migrations/2025_03_10_234704_create_batches_table.php

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tank_id');
            $table->date('entry_date');
            $table->integer('initial_quantity');
            $table->string('species', 100);
            $table->enum('status', ['active', 'finished']);
            $table->enum('cultivation', ['nursery', 'growout']);
            $table->foreign('tank_id')->references('id')->on('tanks')->onDelete('cascade');
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
```
