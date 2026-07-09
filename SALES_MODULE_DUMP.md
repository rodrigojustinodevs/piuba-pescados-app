# Sales Module Code Dump

Gerado automaticamente com os principais arquivos do modulo de sales.

## Arquivos inclusos
- `routes/app/company/sale.php`
- `app/Presentation/Controllers/SaleController.php`
- `app/Presentation/Requests/Sale/SaleStoreRequest.php`
- `app/Presentation/Requests/Sale/SaleUpdateRequest.php`
- `app/Presentation/Resources/Sale/SaleResource.php`
- `app/Application/DTOs/SaleDTO.php`
- `app/Application/DTOs/SaleInputDTO.php`
- `app/Application/DTOs/HarvestSaleDTO.php`
- `app/Application/UseCases/Sale/ListSalesUseCase.php`
- `app/Application/UseCases/Sale/ShowSaleUseCase.php`
- `app/Application/UseCases/Sale/ProcessHarvestSaleUseCase.php`
- `app/Application/UseCases/Sale/UpdateSaleUseCase.php`
- `app/Application/UseCases/Sale/DeleteSaleUseCase.php`
- `app/Application/Actions/Sale/GuardBiomassAction.php`
- `app/Application/Actions/Sale/RegisterBiomassOutflowAction.php`
- `app/Application/Actions/Sale/GenerateReceivableAction.php`
- `app/Domain/Enums/SaleStatus.php`
- `app/Domain/Events/SaleProcessed.php`
- `app/Domain/Models/Sale.php`
- `app/Domain/Repositories/SaleRepositoryInterface.php`
- `app/Infrastructure/Persistence/SaleRepository.php`
- `database/migrations/2025_05_11_114927_create_sales_table.php`
- `database/migrations/2026_03_24_000003_update_sales_table_add_stocking_and_financial.php`
- `database/migrations/2026_03_25_140000_add_sale_reference_type_to_stock_transactions_table.php`

---

## routes/app/company/sale.php

```php
<?php

declare(strict_types=1);

use App\Presentation\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['permission:create-sale'])
    ->post('sale', [SaleController::class, 'store']);

Route::middleware(['permission:view-sale'])
    ->get('sales', [SaleController::class, 'index']);

Route::middleware(['permission:view-sale'])
    ->get('sale/{id}', [SaleController::class, 'show']);

Route::middleware(['permission:update-sale'])
    ->put('sale/{id}', [SaleController::class, 'update']);

Route::middleware(['permission:delete-sale'])
    ->delete('sale/{id}', [SaleController::class, 'destroy']);
```

## app/Presentation/Controllers/SaleController.php

```php
<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Sale\DeleteSaleUseCase;
use App\Application\UseCases\Sale\ListSalesUseCase;
use App\Application\UseCases\Sale\ProcessHarvestSaleUseCase;
use App\Application\UseCases\Sale\ShowSaleUseCase;
use App\Application\UseCases\Sale\UpdateSaleUseCase;
use App\Presentation\Requests\Sale\SaleStoreRequest;
use App\Presentation\Requests\Sale\SaleUpdateRequest;
use App\Presentation\Resources\Sale\SaleResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Sales", description="Vendas")
 * @OA\Schema(
 *     schema="Sale",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="totalWeight", type="number", format="float"),
 *     @OA\Property(property="pricePerKg", type="number", format="float"),
 *     @OA\Property(property="totalRevenue", type="number", format="float"),
 *     @OA\Property(property="saleDate", type="string", format="date"),
 *     @OA\Property(property="status", type="string", enum={"pending","confirmed","cancelled"}),
 *     @OA\Property(property="statusLabel", type="string", example="Pending"),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="batchId", type="string", format="uuid"),
 *     @OA\Property(property="stockingId", type="string", format="uuid", nullable=true),
 *     @OA\Property(
 *         property="company",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(
 *         property="client",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(
 *         property="batch",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(
 *         property="stocking",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="quantity", type="integer"),
 *         @OA\Property(property="averageWeight", type="number", format="float")
 *     ),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
class SaleController
{
    /**
     * @OA\Get(
     *     path="/company/sales",
     *     summary="List sales",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="client_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="batch_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending","confirmed","cancelled"})
     *     ),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Paginated list of sales"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(
        Request $request,
        ListSalesUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only([
                'client_id', 'batch_id', 'status',
                'date_from', 'date_to', 'per_page', 'page',
            ]),
        );

        return ApiResponse::success(
            data:       SaleResource::collection($paginator->items()),
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
     *     path="/company/sale/{id}",
     *     summary="Get sale by ID",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Sale ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Sale found"),
     *     @OA\Response(response=404, description="Sale not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(
        string $id,
        ShowSaleUseCase $useCase,
    ): JsonResponse {
        $sale = $useCase->execute($id);

        return ApiResponse::success(
            data: new SaleResource($sale),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/sale",
     *     summary="Create a sale",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"clientId","batchId","totalWeight","pricePerKg","saleDate"},
     *             @OA\Property(property="companyId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="clientId", type="string", format="uuid"),
     *             @OA\Property(property="batchId", type="string", format="uuid"),
     *             @OA\Property(property="stockingId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="financialCategoryId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="totalWeight", type="number", format="float", minimum=0.001),
     *             @OA\Property(property="pricePerKg", type="number", format="float", minimum=0),
     *             @OA\Property(property="saleDate", type="string", format="date"),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"pending","confirmed","cancelled"},
     *                 nullable=true
     *             ),
     *             @OA\Property(property="notes", type="string", nullable=true),
     *             @OA\Property(property="isTotalHarvest", type="boolean", nullable=true),
     *             @OA\Property(
     *                 property="tolerancePercent",
     *                 type="number",
     *                 format="float",
     *                 minimum=0,
     *                 maximum=50,
     *                 nullable=true
     *             ),
     *             @OA\Property(property="needsInvoice", type="boolean", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Sale created"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Related entities not found")
     * )
     */
    public function store(
        SaleStoreRequest $request,
        ProcessHarvestSaleUseCase $useCase,
    ): JsonResponse {
        $sale = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new SaleResource($sale),
            message: 'Venda registrada com sucesso.',
        );
    }

    /**
     * @OA\Put(
     *     path="/company/sale/{id}",
     *     summary="Update a sale",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Sale ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="clientId", type="string", format="uuid"),
     *             @OA\Property(property="totalWeight", type="number", format="float", minimum=0.001),
     *             @OA\Property(property="pricePerKg", type="number", format="float", minimum=0),
     *             @OA\Property(property="saleDate", type="string", format="date"),
     *             @OA\Property(property="status", type="string", enum={"pending","confirmed","cancelled"}),
     *             @OA\Property(property="notes", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Sale updated"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Sale not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(
        SaleUpdateRequest $request,
        string $id,
        UpdateSaleUseCase $useCase,
    ): JsonResponse {
        $sale = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    new SaleResource($sale),
            message: 'Venda atualizada com sucesso.',
        );
    }

    /**
     * @OA\Delete(
     *     path="/company/sale/{id}",
     *     summary="Delete a sale",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Sale ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Sale deleted"),
     *     @OA\Response(response=404, description="Sale not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(
        string $id,
        DeleteSaleUseCase $useCase,
    ): JsonResponse {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Venda excluída com sucesso.');
    }
}
```

## app/Presentation/Requests/Sale/SaleStoreRequest.php

```php
<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Sale;

use App\Domain\Enums\SaleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SaleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_id'            => ['nullable', 'uuid', 'exists:companies,id'],
            'client_id'             => ['required', 'uuid', 'exists:clients,id'],
            'batch_id'              => ['required', 'uuid', 'exists:batches,id'],
            'stocking_id'           => ['nullable', 'uuid', 'exists:stockings,id'],
            'financial_category_id' => ['nullable', 'uuid', 'exists:financial_categories,id'],
            'total_weight'          => ['required', 'numeric', 'min:0.001'],
            'price_per_kg'          => ['required', 'numeric', 'min:0'],
            'sale_date'             => ['required', 'date'],
            'status'                => ['nullable', new Enum(SaleStatus::class)],
            'notes'                 => ['nullable', 'string'],
            'is_total_harvest'      => ['nullable', 'boolean'],
            'tolerance_percent'     => ['nullable', 'numeric', 'min:0', 'max:50'],
            'needs_invoice'         => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'client_id.required' => 'The customer is required.',
            'client_id.exists'   => 'The selected customer does not exist.',

            'batch_id.required' => 'The batch is required.',
            'batch_id.exists'   => 'The selected batch does not exist.',

            'stocking_id.exists' => 'The selected stocking does not exist.',

            'financial_category_id.exists' => 'The selected financial category does not exist.',

            'total_weight.required' => 'The total weight is required.',
            'total_weight.numeric'  => 'The total weight must be numeric.',
            'total_weight.min'      => 'The total weight must be greater than zero.',

            'price_per_kg.required' => 'The price per kg is required.',
            'price_per_kg.numeric'  => 'The price per kg must be numeric.',
            'price_per_kg.min'      => 'The price per kg must be greater than zero.',

            'sale_date.required' => 'The sale date is required.',
            'sale_date.date'     => 'The sale date must be a valid date.',

            'status.Illuminate\Validation\Rules\Enum' => 'The status must be: pending, confirmed or cancelled.',

            'needs_invoice.boolean'     => 'The needs invoice field must be true or false.',
            'is_total_harvest.boolean'  => 'The total harvest field must be true or false.',
            'tolerance_percent.numeric' => 'The tolerance percent must be numeric.',
            'tolerance_percent.min'     => 'The tolerance percent must be greater than zero.',
            'tolerance_percent.max'     => 'The tolerance percent must be less than or equal to 50.',
        ];
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_id'            => $this->input('company_id', $this->input('companyId')),
            'client_id'             => $this->input('client_id', $this->input('clientId')),
            'batch_id'              => $this->input('batch_id', $this->input('batchId')),
            'stocking_id'           => $this->input('stocking_id', $this->input('stockingId')),
            'financial_category_id' => $this->input('financial_category_id', $this->input('financialCategoryId')),

            'total_weight'      => $this->input('total_weight', $this->input('totalWeight')),
            'price_per_kg'      => $this->input('price_per_kg', $this->input('pricePerKg')),
            'sale_date'         => $this->input('sale_date', $this->input('saleDate')),
            'status'            => $this->input('status'),
            'notes'             => $this->input('notes'),
            'is_total_harvest'  => $this->input('is_total_harvest', $this->input('isTotalHarvest')),
            'tolerance_percent' => $this->input('tolerance_percent', $this->input('tolerancePercent')),
            'needs_invoice'     => $this->input('needs_invoice', $this->input('needsInvoice')),
        ]);
    }
}
```

## app/Presentation/Requests/Sale/SaleUpdateRequest.php

```php
<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Sale;

use App\Domain\Enums\SaleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SaleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_id'    => ['sometimes', 'uuid', 'exists:clients,id'],
            'total_weight' => ['sometimes', 'numeric', 'min:0.001'],
            'price_per_kg' => ['sometimes', 'numeric', 'min:0'],
            'sale_date'    => ['sometimes', 'date'],
            'status'       => ['sometimes', new Enum(SaleStatus::class)],
            'notes'        => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'client_id.exists'                        => 'The selected customer does not exist.',
            'total_weight.min'                        => 'The total weight must be greater than zero.',
            'price_per_kg.min'                        => 'The price per kg must be greater than zero.',
            'sale_date.date'                          => 'The sale date must be a valid date.',
            'status.Illuminate\Validation\Rules\Enum' => 'The status must be: pending, confirmed or cancelled.',
        ];
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $this->merge([
            'client_id'    => $this->input('client_id', $this->input('clientId')),
            'total_weight' => $this->input('total_weight', $this->input('totalWeight')),
            'price_per_kg' => $this->input('price_per_kg', $this->input('pricePerKg')),
            'sale_date'    => $this->input('sale_date', $this->input('saleDate')),
            'status'       => $this->input('status'),
            'notes'        => $this->input('notes'),
        ]);
    }
}
```

## app/Presentation/Resources/Sale/SaleResource.php

```php
<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Sale;

use App\Domain\Enums\SaleStatus;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string                          $id
 * @property-read string                          $company_id
 * @property-read string                          $client_id
 * @property-read string                          $batch_id
 * @property-read string|null                     $stocking_id
 * @property-read string|null                     $financial_category_id
 * @property-read float                           $total_weight
 * @property-read float                           $price_per_kg
 * @property-read float                           $total_revenue
 * @property-read \Illuminate\Support\Carbon      $sale_date
 * @property-read SaleStatus                      $status
 * @property-read string|null                     $notes
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Company|null $company
 * @property-read \App\Domain\Models\Client|null  $client
 * @property-read \App\Domain\Models\Batch|null   $batch
 * @property-read \App\Domain\Models\Stocking|null $stocking
 */
class SaleResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray($request): array
    {
        $status = $this->status;

        return [
            'id'           => $this->id,
            'totalWeight'  => (float) $this->total_weight,
            'pricePerKg'   => (float) $this->price_per_kg,
            'totalRevenue' => (float) $this->total_revenue,
            'saleDate'     => $this->sale_date->toDateString(),
            'status'       => $status->value,
            'statusLabel'  => $status->label(),
            'notes'        => $this->notes,
            'batchId'      => $this->batch_id,
            'stockingId'   => $this->stocking_id,
            'company'      => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),
            'client' => $this->whenLoaded('client', fn (): array => [
                'id'   => $this->client->id,
                'name' => $this->client->name,
            ]),
            'batch' => $this->whenLoaded('batch', fn (): array => [
                'id'   => $this->batch->id,
                'name' => $this->batch->name,
            ]),
            'stocking' => $this->whenLoaded('stocking', fn (): ?array => $this->stocking
                ? [
                    'id'            => $this->stocking->id,
                    'quantity'      => $this->stocking->quantity,
                    'averageWeight' => $this->stocking->average_weight,
                ]
                : null),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
```

## app/Application/DTOs/SaleDTO.php

```php
<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class SaleDTO
{
    /**
     * @param  array{name?: string|null}|null  $company
     * @param  array{name?: string|null}|null  $client
     */
    public function __construct(
        public string $id,
        public float $totalWeight,
        public float $pricePerKg,
        public float $totalRevenue,
        public string $saleDate,
        public ?array $company,
        public ?array $client,
        public string $batchId,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            totalWeight: $data['total_weight'],
            pricePerKg: $data['price_per_kg'],
            totalRevenue: $data['total_revenue'],
            saleDate: $data['sale_date'],
            company: isset($data['company']) ? ['name' => $data['company']['name'] ?? null] : null,
            client: isset($data['client']) ? [
                'id'   => $data['client']['id'] ?? null,
                'name' => $data['client']['name'] ?? null,
            ] : null,
            batchId: $data['batch_id'],
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'totalWeight'  => $this->totalWeight,
            'pricePerKg'   => $this->pricePerKg,
            'totalRevenue' => $this->totalRevenue,
            'saleDate'     => $this->saleDate,
            'company'      => $this->company,
            'client'       => $this->client,
            'batchId'      => $this->batchId,
            'createdAt'    => $this->createdAt,
            'updatedAt'    => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->id === '' || $this->totalWeight <= 0 || $this->pricePerKg <= 0;
    }
}
```

## app/Application/DTOs/SaleInputDTO.php

```php
<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\SaleStatus;

final readonly class SaleInputDTO
{
    public function __construct(
        public string $companyId,
        public string $clientId,
        public string $batchId,
        public float $totalWeight,
        public float $pricePerKg,
        public string $saleDate,
        public ?string $stockingId = null,
        public ?string $financialCategoryId = null,
        public SaleStatus $status = SaleStatus::PENDING,
        public ?string $notes = null,
        public bool $isHarvestTotal = false,
    ) {
    }

    public function totalRevenue(): float
    {
        return round($this->totalWeight * $this->pricePerKg, 2);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            companyId:           (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            clientId:            (string) ($data['client_id'] ?? $data['clientId'] ?? ''),
            batchId:             (string) ($data['batch_id'] ?? $data['batchId'] ?? ''),
            totalWeight:         (float) ($data['total_weight'] ?? $data['totalWeight'] ?? 0),
            pricePerKg:          (float) ($data['price_per_kg'] ?? $data['pricePerKg'] ?? 0),
            saleDate:            (string) ($data['sale_date'] ?? $data['saleDate'] ?? ''),
            stockingId:          isset($data['stocking_id']) ? (string) $data['stocking_id']
                               : (isset($data['stockingId']) ? (string) $data['stockingId'] : null),
            financialCategoryId: isset($data['financial_category_id']) ? (string) $data['financial_category_id']
                               : (isset($data['financialCategoryId']) ? (string) $data['financialCategoryId'] : null),
            status:              isset($data['status'])
                               ? SaleStatus::from((string) $data['status'])
                               : SaleStatus::PENDING,
            notes:               isset($data['notes']) ? (string) $data['notes'] : null,
            isHarvestTotal:      (bool) ($data['is_total_harvest'] ?? $data['isHarvestTotal'] ?? false),
        );
    }
}
```

## app/Application/DTOs/HarvestSaleDTO.php

```php
<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\SaleStatus;

/**
 * DTO específico para o fluxo de despesca/venda com validação biológica,
 * baixa de estoque e gestão do ciclo de vida do lote.
 */
final readonly class HarvestSaleDTO
{
    public function __construct(
        public string $companyId,
        public string $clientId,
        public string $batchId,
        public float $totalWeight,
        public float $pricePerKg,
        public string $saleDate,
        public bool $isHarvestTotal,
        public ?string $stockingId = null,
        public ?string $financialCategoryId = null,
        public SaleStatus $status = SaleStatus::PENDING,
        public ?string $notes = null,
        public float $tolerancePercent = 5.0,
        public bool $needsInvoice = false,
    ) {
    }

    public function totalRevenue(): float
    {
        return round($this->totalWeight * $this->pricePerKg, 2);
    }

    /**
     * Upper biomass limit including the configured tolerance margin.
     * Example: if biomass = 1000 kg and tolerancePercent = 5, limit = 1050 kg.
     */
    public function biomassLimitWithTolerance(float $availableBiomass): float
    {
        return $availableBiomass * (1 + $this->tolerancePercent / 100);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            companyId:           (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            clientId:            (string) ($data['client_id'] ?? $data['clientId'] ?? ''),
            batchId:             (string) ($data['batch_id'] ?? $data['batchId'] ?? ''),
            totalWeight:         (float) ($data['total_weight'] ?? $data['totalWeight'] ?? 0),
            pricePerKg:          (float) ($data['price_per_kg'] ?? $data['pricePerKg'] ?? 0),
            saleDate:            (string) ($data['sale_date'] ?? $data['saleDate'] ?? ''),
            isHarvestTotal:      (bool) ($data['is_total_harvest'] ?? $data['isHarvestTotal'] ?? false),
            stockingId:          isset($data['stocking_id']) ? (string) $data['stocking_id']
                               : (isset($data['stockingId']) ? (string) $data['stockingId'] : null),
            financialCategoryId: isset($data['financial_category_id']) ? (string) $data['financial_category_id']
                               : (isset($data['financialCategoryId']) ? (string) $data['financialCategoryId'] : null),
            status:              isset($data['status'])
                               ? SaleStatus::from((string) $data['status'])
                               : SaleStatus::PENDING,
            notes:               isset($data['notes']) ? (string) $data['notes'] : null,
            tolerancePercent:    (float) ($data['tolerance_percent'] ?? $data['tolerancePercent'] ?? 5.0),
            needsInvoice:        (bool) ($data['needs_invoice'] ?? $data['needsInvoice'] ?? false),
        );
    }

    /**
     * Converts this DTO to a SaleInputDTO for persistence via SaleRepository.
     */
    public function toSaleInputDTO(): SaleInputDTO
    {
        return new SaleInputDTO(
            companyId:           $this->companyId,
            clientId:            $this->clientId,
            batchId:             $this->batchId,
            totalWeight:         $this->totalWeight,
            pricePerKg:          $this->pricePerKg,
            saleDate:            $this->saleDate,
            stockingId:          $this->stockingId,
            financialCategoryId: $this->financialCategoryId,
            status:              $this->status,
            notes:               $this->notes,
            isHarvestTotal:      $this->isHarvestTotal,
        );
    }
}
```

## app/Application/UseCases/Sale/ListSalesUseCase.php

```php
<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SaleRepositoryInterface;

final readonly class ListSalesUseCase
{
    public function __construct(
        private SaleRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        $filters['company_id'] = $this->companyResolver->resolve();

        return $this->repository->paginate($filters);
    }
}
```

## app/Application/UseCases/Sale/ShowSaleUseCase.php

```php
<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Domain\Models\Sale;
use App\Domain\Repositories\SaleRepositoryInterface;

final readonly class ShowSaleUseCase
{
    public function __construct(
        private SaleRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): Sale
    {
        return $this->repository->findOrFail($id);
    }
}
```

## app/Application/UseCases/Sale/ProcessHarvestSaleUseCase.php

```php
<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\Actions\Client\GuardClientCreditAction;
use App\Application\Actions\Sale\GenerateReceivableAction;
use App\Application\Actions\Sale\GuardBiomassAction;
use App\Application\Actions\Sale\RegisterBiomassOutflowAction;
use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\HarvestSaleDTO;
use App\Domain\Events\SaleProcessed;
use App\Domain\Exceptions\ClientMissingFiscalDataException;
use App\Domain\Exceptions\ClosedStockingException;
use App\Domain\Models\Client;
use App\Domain\Models\Sale;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Motor principal de despesca e venda.
 *
 * Dentro de um único DB::transaction:
 *   1. Valida que a estocagem não está encerrada.
 *   2. Valida biomassa disponível com margem de tolerância.
 *   3. Persiste a venda.
 *   4. Registra baixa no livro-razão (direction=out) com custo unitário acumulado.
 *   5. Encerra a estocagem quando is_total_harvest = true.
 *   6. Gera "Contas a Receber" (FinancialTransaction PENDING, reference_type=sale).
 */
final readonly class ProcessHarvestSaleUseCase
{
    public function __construct(
        private SaleRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
        private GuardBiomassAction $guardBiomass,
        private RegisterBiomassOutflowAction $registerOutflow,
        private GenerateReceivableAction $generateReceivable,
        private GuardClientCreditAction $guardClientCredit,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados validados pelo FormRequest
     */
    public function execute(array $data): Sale
    {
        $data['company_id'] = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? $data['companyId'] ?? null,
        );

        $dto = HarvestSaleDTO::fromArray($data);

        return DB::transaction(function () use ($dto): Sale {
            if ($dto->stockingId !== null) {
                return $this->processWithStocking($dto);
            }

            return $this->processWithoutStocking($dto);
        });
    }

    /**
     * Fluxo completo: validação de biomassa → venda → baixa → ciclo de vida → recebível.
     */
    private function processWithStocking(HarvestSaleDTO $dto): Sale
    {
        /** @var Stocking $stocking */
        $stocking = Stocking::findOrFail($dto->stockingId);

        if ($stocking->isClosed()) {
            throw new ClosedStockingException($stocking->id);
        }

        // Passo 0: Valida dados fiscais se emissão de nota fiscal solicitada
        $this->guardClientFiscalData($dto);

        // Passo 1a: Valida limite de crédito do cliente
        $this->guardClientCredit->execute($dto->clientId, $dto->totalRevenue());

        // Passo 1b: Valida biomassa com tolerância configurável
        $this->guardBiomass->executeWithTolerance(
            stocking:         $stocking,
            requestedWeight:  $dto->totalWeight,
            tolerancePercent: $dto->tolerancePercent,
        );

        // Passo 2: Persiste a venda
        $sale = $this->repository->create($dto->toSaleInputDTO());

        // Passo 3: Baixa de biomassa no livro-razão
        // Calcula peso já vendido ANTES desta venda (exclui a atual do cálculo)
        $alreadySoldWeight = $this->repository->soldWeightByStocking(
            stockingId:    $stocking->id,
            excludeSaleId: $sale->id,
        );

        $this->registerOutflow->execute($stocking, $sale, $alreadySoldWeight);

        // Passo 4: Encerra a estocagem se for despesca total
        if ($dto->isHarvestTotal) {
            $stocking->markAsClosed();
        }

        // Passo 5: Gera Contas a Receber
        $this->generateReceivable->execute($dto->toSaleInputDTO(), $sale);

        // Passo 6: Dispara evento para gerar histórico automático no lote
        SaleProcessed::dispatch($sale);

        return $sale;
    }

    /**
     * Venda simples sem estocagem vinculada: persiste + recebível apenas.
     */
    private function processWithoutStocking(HarvestSaleDTO $dto): Sale
    {
        $this->guardClientFiscalData($dto);
        $this->guardClientCredit->execute($dto->clientId, $dto->totalRevenue());

        $sale = $this->repository->create($dto->toSaleInputDTO());

        $this->generateReceivable->execute($dto->toSaleInputDTO(), $sale);

        // Dispara mesmo sem stocking; o listener ignora quando stocking_id é null
        SaleProcessed::dispatch($sale);

        return $sale;
    }

    /**
     * Se needs_invoice for true, exige que o cliente tenha document_number e address.
     *
     * @throws ClientMissingFiscalDataException
     */
    private function guardClientFiscalData(HarvestSaleDTO $dto): void
    {
        if (! $dto->needsInvoice) {
            return;
        }

        /** @var Client|null $client */
        $client = Client::find($dto->clientId);

        if (
            $client === null
            || empty($client->document_number)
            || empty($client->address)
        ) {
            throw new ClientMissingFiscalDataException($dto->clientId);
        }
    }
}
```

## app/Application/UseCases/Sale/UpdateSaleUseCase.php

```php
<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\Actions\Sale\GuardBiomassAction;
use App\Domain\Enums\SaleStatus;
use App\Domain\Models\Sale;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateSaleUseCase
{
    public function __construct(
        private SaleRepositoryInterface $repository,
        private GuardBiomassAction $guardBiomass,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados validados pelo FormRequest
     */
    public function execute(string $id, array $data): Sale
    {
        $sale = $this->repository->findOrFail($id);

        $attributes = $this->buildAttributes($sale, $data);

        return DB::transaction(
            fn (): Sale => $this->repository->update($id, $attributes)
        );
    }

    /**
     * Mescla o estado atual da venda com o patch recebido.
     * Campos ausentes em $data não são sobrescritos.
     * Revalida biomassa quando total_weight muda.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function buildAttributes(Sale $sale, array $data): array
    {
        $attributes = [];

        if (array_key_exists('client_id', $data)) {
            $attributes['client_id'] = $data['client_id'];
        }

        if (array_key_exists('total_weight', $data)) {
            $newWeight  = (float) $data['total_weight'];
            $stockingId = $data['stocking_id'] ?? $sale->stocking_id;

            // Revalida biomassa excluindo o peso já comprometido por esta venda
            if ($stockingId !== null) {
                /** @var Stocking $stocking */
                $stocking = Stocking::findOrFail((string) $stockingId);

                $this->guardBiomass->execute(
                    stocking:        $stocking,
                    requestedWeight: $newWeight,
                    excludeSaleId:   $sale->id,
                );
            }

            $attributes['total_weight'] = $newWeight;
        }

        if (array_key_exists('price_per_kg', $data)) {
            $attributes['price_per_kg'] = (float) $data['price_per_kg'];
        }

        // Recalcula total_revenue se peso ou preço mudaram
        if (isset($attributes['total_weight']) || isset($attributes['price_per_kg'])) {
            $weight = $attributes['total_weight'] ?? (float) $sale->total_weight;
            $price  = $attributes['price_per_kg'] ?? (float) $sale->price_per_kg;

            $attributes['total_revenue'] = round($weight * $price, 2);
        }

        if (array_key_exists('sale_date', $data)) {
            $attributes['sale_date'] = $data['sale_date'];
        }

        if (array_key_exists('status', $data)) {
            $attributes['status'] = SaleStatus::from((string) $data['status'])->value;
        }

        if (array_key_exists('notes', $data)) {
            $attributes['notes'] = $data['notes'];
        }

        return $attributes;
    }
}
```

## app/Application/UseCases/Sale/DeleteSaleUseCase.php

```php
<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteSaleUseCase
{
    public function __construct(
        private SaleRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): void
    {
        $this->repository->findOrFail($id);

        DB::transaction(function () use ($id): void {
            $this->repository->delete($id);
        });
    }
}
```

## app/Application/Actions/Sale/GuardBiomassAction.php

```php
<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Exceptions\InsufficientBiomassException;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\Log;

final readonly class GuardBiomassAction
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
    ) {
    }

    /**
     * Valida biomassa disponível sem margem de tolerância.
     * Usada em fluxos simples de venda sem despesca total.
     *
     * @throws InsufficientBiomassException
     */
    public function execute(
        Stocking $stocking,
        float $requestedWeight,
        ?string $excludeSaleId = null,
    ): void {
        $available = $stocking->initialBiomass()
            - $this->saleRepository->soldWeightByStocking($stocking->id, $excludeSaleId);

        if ($requestedWeight > $available) {
            throw new InsufficientBiomassException(
                available:  $available,
                requested:  $requestedWeight,
                stockingId: $stocking->id,
            );
        }
    }

    /**
     * Valida biomassa com margem de tolerância configurável.
     * Usada em despescas onde há variação natural de peso.
     *
     * Acima da estimativa mas dentro da tolerância → warning (não bloqueia).
     * Acima da tolerância → InsufficientBiomassException.
     *
     * @throws InsufficientBiomassException
     */
    public function executeWithTolerance(
        Stocking $stocking,
        float $requestedWeight,
        float $tolerancePercent,
        ?string $excludeSaleId = null,
    ): void {
        $committedWeight = $this->saleRepository->soldWeightByStocking($stocking->id, $excludeSaleId);
        $available       = $stocking->initialBiomass() - $committedWeight;
        $toleranceLimit  = $available * (1 + $tolerancePercent / 100);

        if ($requestedWeight > $toleranceLimit) {
            throw new InsufficientBiomassException(
                available:  $toleranceLimit,
                requested:  $requestedWeight,
                stockingId: $stocking->id,
            );
        }

        if ($requestedWeight > $available) {
            Log::warning('Harvest adjustment: requested weight exceeds biomass estimate but is within tolerance.', [
                'stocking_id'       => $stocking->id,
                'available_kg'      => $available,
                'requested_kg'      => $requestedWeight,
                'tolerance_percent' => $tolerancePercent,
                'tolerance_limit'   => $toleranceLimit,
                'excess_kg'         => round($requestedWeight - $available, 4),
            ]);
        }
    }
}
```

## app/Application/Actions/Sale/RegisterBiomassOutflowAction.php

```php
<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Application\Actions\Stock\RegisterStockTransactionAction;
use App\Application\DTOs\StockTransactionDTO;
use App\Domain\Enums\StockTransactionDirection;
use App\Domain\Enums\StockTransactionReferenceType;
use App\Domain\Enums\Unit;
use App\Domain\Models\Sale;
use App\Domain\Models\Stocking;
use App\Domain\Models\StockTransaction;

final readonly class RegisterBiomassOutflowAction
{
    public function __construct(
        private RegisterStockTransactionAction $registerStockTransaction,
    ) {
    }

    /**
     * Registra a saída de biomassa no livro-razão (stock_transactions).
     *
     * O unit_price é o custo unitário acumulado do lote, calculado com base
     * no peso já vendido anteriormente — isso garante o custo correto por kg
     * para cálculo de lucro por tanque.
     *
     * @param float $alreadySoldWeight Peso vendido ANTES desta venda (exclui a atual)
     */
    public function execute(
        Stocking $stocking,
        Sale $sale,
        float $alreadySoldWeight,
    ): StockTransaction {
        $unitCost  = $stocking->calculateCurrentUnitCost($alreadySoldWeight);
        $totalCost = round((float) $sale->total_weight * $unitCost, 2);

        return $this->registerStockTransaction->execute(new StockTransactionDTO(
            companyId:     (string) $sale->company_id,
            quantity:      (float)  $sale->total_weight,
            unitPrice:     $unitCost,
            totalCost:     $totalCost,
            unit:          Unit::KG,
            direction:     StockTransactionDirection::OUT,
            supplyId:      null,
            referenceId:   (string) $sale->id,
            referenceType: StockTransactionReferenceType::SALE,
        ));
    }
}
```

## app/Application/Actions/Sale/GenerateReceivableAction.php

```php
<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Application\DTOs\FinancialTransactionInputDTO;
use App\Application\DTOs\SaleInputDTO;
use App\Application\Services\FinancialTransactionService;
use App\Domain\Enums\FinancialTransactionReferenceType;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;
use App\Domain\Models\Sale;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;

final readonly class GenerateReceivableAction
{
    public function __construct(
        private FinancialTransactionRepositoryInterface $transactionRepository,
        private FinancialTransactionService $transactionService,
    ) {
    }

    /**
     * Gera automaticamente o "Contas a Receber" (PENDING) vinculado à venda.
     * Não executa se financialCategoryId for null — venda sem vinculação financeira.
     *
     * Valida que a categoria é do tipo REVENUE antes de persistir,
     * usando FinancialTransactionService como fonte única dessa regra.
     */
    public function execute(SaleInputDTO $dto, Sale $sale): void
    {
        if ($dto->financialCategoryId === null) {
            return;
        }

        // Valida que a categoria é REVENUE — regra de negócio do domínio financeiro
        $this->transactionService->validateCategoryType(
            categoryId:      $dto->financialCategoryId,
            transactionType: FinancialType::REVENUE,
        );

        $receivableDTO = new FinancialTransactionInputDTO(
            companyId:           $dto->companyId,
            financialCategoryId: $dto->financialCategoryId,
            type:                FinancialType::REVENUE,
            amount:              $dto->totalRevenue(),
            dueDate:             $dto->saleDate,
            status:              FinancialTransactionStatus::PENDING,
            paymentDate:         null, // status PENDING → sem data de pagamento
            description:         "Contas a Receber — Venda #{$sale->id}",
            referenceType:       FinancialTransactionReferenceType::SALE,
            referenceId:         (string) $sale->id,
        );

        // Aplica regras de payment_date (no-op aqui pois status = PENDING)
        $resolvedDTO = $this->transactionService->applyPaymentDateToDTO($receivableDTO);

        $this->transactionRepository->create($resolvedDTO);
    }
}
```

## app/Domain/Enums/SaleStatus.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum SaleStatus: string
{
    case PENDING   = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    public function isConfirmed(): bool
    {
        return $this === self::CONFIRMED;
    }
}
```

## app/Domain/Events/SaleProcessed.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\Models\Sale;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired by ProcessHarvestSaleUseCase after a sale is successfully persisted.
 * Listened to by GenerateStockingHistory when the sale is linked to a stocking (despesca).
 */
final readonly class SaleProcessed implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Sale $sale,
    ) {
    }
}
```

## app/Domain/Models/Sale.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string          $id
 * @property string          $company_id
 * @property string          $client_id
 * @property string          $batch_id
 * @property string|null     $stocking_id
 * @property string|null     $financial_category_id
 * @property float           $total_weight
 * @property float           $price_per_kg
 * @property float           $total_revenue
 * @property Carbon          $sale_date
 * @property SaleStatus      $status
 * @property string|null     $notes
 * @property-read Company|null          $company
 * @property-read Client|null           $client
 * @property-read Batch|null            $batch
 * @property-read Stocking|null         $stocking
 * @property-read FinancialCategory|null $financialCategory
 * @property Carbon|null     $created_at
 * @property Carbon|null     $updated_at
 * @property Carbon|null     $deleted_at
 */
class Sale extends BaseModel
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'client_id',
        'batch_id',
        'stocking_id',
        'financial_category_id',
        'total_weight',
        'price_per_kg',
        'total_revenue',
        'sale_date',
        'status',
        'notes',
        'is_total_harvest',
    ];

    /** @var array<string, string|class-string> */
    protected $casts = [
        'status'           => SaleStatus::class,
        'total_weight'     => 'float',
        'price_per_kg'     => 'decimal:2',
        'total_revenue'    => 'decimal:2',
        'sale_date'        => 'date:Y-m-d',
        'is_total_harvest' => 'boolean',
    ];

    #[\Override]
    protected static function booted(): void
    {
        static::creating(static function (Sale $sale): void {
            $sale->id ??= (string) Str::uuid();
            $sale->status ??= SaleStatus::PENDING;
        });
    }

    /** @phpstan-return BelongsTo<Company, static> */
    public function company(): BelongsTo
    {
        /** @var BelongsTo<Company, static> $relation */
        $relation = $this->belongsTo(Company::class, 'company_id');

        return $relation;
    }

    /** @phpstan-return BelongsTo<Client, static> */
    public function client(): BelongsTo
    {
        /** @var BelongsTo<Client, static> $relation */
        $relation = $this->belongsTo(Client::class, 'client_id');

        return $relation;
    }

    /** @phpstan-return BelongsTo<Batch, static> */
    public function batch(): BelongsTo
    {
        /** @var BelongsTo<Batch, static> $relation */
        $relation = $this->belongsTo(Batch::class, 'batch_id');

        return $relation;
    }

    /** @phpstan-return BelongsTo<Stocking, static> */
    public function stocking(): BelongsTo
    {
        /** @var BelongsTo<Stocking, static> $relation */
        $relation = $this->belongsTo(Stocking::class, 'stocking_id');

        return $relation;
    }

    /** @phpstan-return BelongsTo<FinancialCategory, static> */
    public function financialCategory(): BelongsTo
    {
        /** @var BelongsTo<FinancialCategory, static> $relation */
        $relation = $this->belongsTo(FinancialCategory::class, 'financial_category_id');

        return $relation;
    }

    /** @phpstan-return HasMany<FinancialTransaction, static> */
    public function financialTransactions(): HasMany
    {
        /** @var HasMany<FinancialTransaction, static> $relation */
        $relation = $this->hasMany(FinancialTransaction::class, 'reference_id')
            ->where('reference_type', 'sale');

        return $relation;
    }

    public function totalRevenue(): float
    {
        return round((float) $this->total_weight * (float) $this->price_per_kg, 2);
    }
}
```

## app/Domain/Repositories/SaleRepositoryInterface.php

```php
<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\SaleInputDTO;
use App\Domain\Models\Sale;

interface SaleRepositoryInterface
{
    /**
     * Create a new sale record.
     */
    public function create(SaleInputDTO $dto): Sale;

    /**
     * Update an existing sale record.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Sale;

    /**
     * Delete a sale record (soft delete).
     */
    public function delete(string $id): bool;

    /**
     * Find a sale by ID or throw ModelNotFoundException.
     */
    public function findOrFail(string $id): Sale;

    /**
     * Paginate sales filtered by company.
     *
     * @param array{
     *     company_id: string,
     *     client_id?: string|null,
     *     batch_id?: string|null,
     *     status?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;

    /**
     * Total kg already sold from a given stocking (excluding soft-deleted and cancelled records).
     * Pass $excludeSaleId to omit the current sale when re-validating on update.
     */
    public function soldWeightByStocking(string $stockingId, ?string $excludeSaleId = null): float;
}
```

## app/Infrastructure/Persistence/SaleRepository.php

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\SaleInputDTO;
use App\Domain\Enums\SaleStatus;
use App\Domain\Models\Sale;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SaleRepositoryInterface;

final class SaleRepository implements SaleRepositoryInterface
{
    public function create(SaleInputDTO $dto): Sale
    {
        /** @var Sale $sale */
        $sale = Sale::create([
            'company_id'            => $dto->companyId,
            'client_id'             => $dto->clientId,
            'batch_id'              => $dto->batchId,
            'stocking_id'           => $dto->stockingId,
            'financial_category_id' => $dto->financialCategoryId,
            'total_weight'          => $dto->totalWeight,
            'price_per_kg'          => $dto->pricePerKg,
            'total_revenue'         => $dto->totalRevenue(),
            'sale_date'             => $dto->saleDate,
            'status'                => $dto->status->value,
            'notes'                 => $dto->notes,
            'is_total_harvest'      => $dto->isHarvestTotal,
        ]);

        return $sale->load(['company:id,name', 'client:id,name', 'batch:id,name', 'stocking']);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Sale
    {
        $sale = $this->findOrFail($id);

        $sale->update($attributes);

        return $sale->refresh()->load(['company:id,name', 'client:id,name', 'batch:id,name', 'stocking']);
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function findOrFail(string $id): Sale
    {
        return Sale::with([
            'company:id,name',
            'client:id,name',
            'batch:id,name',
            'stocking',
        ])->findOrFail($id);
    }

    /**
     * @param array{
     *     company_id: string,
     *     client_id?: string|null,
     *     batch_id?: string|null,
     *     status?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = Sale::with([
            'company:id,name',
            'client:id,name',
            'batch:id,name',
        ])
            ->where('company_id', $filters['company_id'])
            ->when(
                ! empty($filters['client_id']),
                static fn ($q) => $q->where('client_id', $filters['client_id']),
            )
            ->when(
                ! empty($filters['batch_id']),
                static fn ($q) => $q->where('batch_id', $filters['batch_id']),
            )
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where(
                    'status',
                    SaleStatus::from((string) $filters['status'])->value,
                ),
            )
            ->when(
                ! empty($filters['date_from']),
                static fn ($q) => $q->whereDate('sale_date', '>=', $filters['date_from']),
            )
            ->when(
                ! empty($filters['date_to']),
                static fn ($q) => $q->whereDate('sale_date', '<=', $filters['date_to']),
            )
            ->latest('sale_date')
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function soldWeightByStocking(string $stockingId, ?string $excludeSaleId = null): float
    {
        return (float) Sale::where('stocking_id', $stockingId)
            ->whereNotIn('status', [SaleStatus::CANCELLED->value])
            ->when(
                $excludeSaleId !== null,
                static fn ($q) => $q->where('id', '!=', $excludeSaleId),
            )
            ->sum('total_weight');
    }
}
```

## database/migrations/2025_05_11_114927_create_sales_table.php

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
        Schema::create('sales', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('client_id');
            $table->uuid('batche_id');
            $table->float('total_weight');
            $table->float('price_per_kg');
            $table->float('total_revenue');
            $table->date('sale_date');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('batche_id')->references('id')->on('batches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
```

## database/migrations/2026_03_24_000003_update_sales_table_add_stocking_and_financial.php

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        $hadBatcheColumn = Schema::hasColumn('sales', 'batche_id');

        Schema::table('sales', function (Blueprint $table): void {
            // Fix original typo: batche_id → batch_id
            if (Schema::hasColumn('sales', 'batche_id')) {
                $table->dropForeign(['batche_id']);
                $table->renameColumn('batche_id', 'batch_id');
            }
        });

        Schema::table('sales', function (Blueprint $table): void {
            if (! Schema::hasColumn('sales', 'stocking_id')) {
                // Link to the specific stocking event (which provides biomass)
                $table->uuid('stocking_id')->nullable()->after('batch_id');
                $table->foreign('stocking_id')->references('id')->on('stockings')->onDelete('set null');
            }

            if (! Schema::hasColumn('sales', 'financial_category_id')) {
                // Revenue category used to auto-generate the receivable
                $table->uuid('financial_category_id')->nullable()->after('stocking_id');
                $table->foreign('financial_category_id')
                    ->references('id')
                    ->on('financial_categories')
                    ->onDelete('set null');
            }

            if (! Schema::hasColumn('sales', 'status')) {
                $table->enum('status', ['pending', 'confirmed', 'cancelled'])
                    ->default('pending')
                    ->after('sale_date');
            }

            if (! Schema::hasColumn('sales', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });

        if ($hadBatcheColumn && Schema::hasColumn('sales', 'batch_id')) {
            Schema::table('sales', function (Blueprint $table): void {
                // Recreates FK only when column was renamed in this migration.
                $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            if (Schema::hasColumn('sales', 'stocking_id')) {
                $table->dropForeign(['stocking_id']);
            }

            if (Schema::hasColumn('sales', 'financial_category_id')) {
                $table->dropForeign(['financial_category_id']);
            }

            $columnsToDrop = array_values(array_filter([
                Schema::hasColumn('sales', 'stocking_id') ? 'stocking_id' : null,
                Schema::hasColumn('sales', 'financial_category_id') ? 'financial_category_id' : null,
                Schema::hasColumn('sales', 'status') ? 'status' : null,
                Schema::hasColumn('sales', 'notes') ? 'notes' : null,
            ]));

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }

            if (Schema::hasColumn('sales', 'batche_id') === false && Schema::hasColumn('sales', 'batch_id')) {
                $table->dropForeign(['batch_id']);
                $table->renameColumn('batch_id', 'batche_id');
                $table->foreign('batche_id')->references('id')->on('batches')->onDelete('cascade');
            }
        });
    }
};
```

## database/migrations/2026_03_25_140000_add_sale_reference_type_to_stock_transactions_table.php

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE stock_transactions
             MODIFY COLUMN reference_type
             ENUM('purchase_item','feeding','adjustment','transfer','stocking','sale') NOT NULL"
        );
    }

    public function down(): void
    {
        DB::table('stock_transactions')
            ->where('reference_type', 'sale')
            ->update([
                'reference_type' => 'stocking',
            ]);

        DB::statement(
            "ALTER TABLE stock_transactions
             MODIFY COLUMN reference_type
             ENUM('purchase_item','feeding','adjustment','transfer','stocking') NOT NULL"
        );
    }
};
```
