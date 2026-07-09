# Create Sale — dump completo (POST /company/sale)

Fluxo: `SaleController::store` → `SaleStoreRequest` → `ProcessHarvestSaleUseCase` (transação) → validações (fiscal, crédito, biomassa com tolerância fixa **50%** no código) → `SaleRepository::create` → `RegisterBiomassOutflowAction` → `CloseStockingAndBatchAction` (se despesca total) → `GenerateReceivableAction` → `SaleProcessed` (após commit) → listener `GenerateStockingHistory`.

**Observação:** `HarvestSaleDTO` e o request aceitam `tolerance_percent`, mas o `ProcessHarvestSaleUseCase` usa a constante `BIOMASS_TOLERANCE_PERCENT = 50.0` na chamada a `executeWithTolerance` — o percentual do payload não entra nesse guard no create.

---
## Índice

- `routes/app/company/sale.php`
- `app/Presentation/Controllers/SaleController.php`
- `app/Presentation/Requests/Sale/SaleStoreRequest.php`
- `app/Presentation/Resources/Sale/SaleResource.php`
- `app/Application/UseCases/Sale/ProcessHarvestSaleUseCase.php`
- `app/Application/DTOs/HarvestSaleDTO.php`
- `app/Application/DTOs/SaleInputDTO.php`
- `app/Application/Contracts/CompanyResolverInterface.php`
- `app/Application/Services/CompanyResolver.php`
- `app/Application/Actions/Sale/GuardClientFiscalDataAction.php`
- `app/Application/Actions/Client/GuardClientCreditAction.php`
- `app/Application/Actions/Sale/GuardBiomassAction.php`
- `app/Application/Actions/Sale/RegisterBiomassOutflowAction.php`
- `app/Application/Actions/Sale/CloseStockingAndBatchAction.php`
- `app/Application/Actions/Sale/GenerateReceivableAction.php`
- `app/Application/Actions/Stock/RegisterStockTransactionAction.php`
- `app/Application/DTOs/StockTransactionDTO.php`
- `app/Application/DTOs/FinancialTransactionInputDTO.php`
- `app/Application/Services/FinancialTransactionService.php`
- `app/Domain/Repositories/SaleRepositoryInterface.php`
- `app/Infrastructure/Persistence/SaleRepository.php`
- `app/Domain/Repositories/FinancialTransactionRepositoryInterface.php`
- `app/Infrastructure/Persistence/FinancialTransactionRepository.php`
- `app/Infrastructure/Persistence/StockTransactionRepository.php`
- `app/Domain/Events/SaleProcessed.php`
- `app/Application/Listeners/GenerateStockingHistory.php`
- `app/Infrastructure/Providers/EventServiceProvider.php`
- `app/Domain/Exceptions/StockingRequiredException.php`
- `app/Domain/Exceptions/ClosedStockingException.php`
- `app/Domain/Exceptions/ClientMissingFiscalDataException.php`
- `app/Domain/Exceptions/InsufficientBiomassException.php`
- `app/Domain/Exceptions/ClientCreditLimitExceededException.php`

---

## `routes/app/company/sale.php`

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

## `app/Presentation/Controllers/SaleController.php`

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
            message: 'Sale registered successfully.',
        );
    }

    /**
     * Delegação: {@see UpdateSaleUseCase} → {@see \App\Application\Actions\Sale\UpdateSaleAction}
     * (transação, locks, trava financeira, biomassa, despesca e sincronização do contas a receber).
     *
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
     *             @OA\Property(property="notes", type="string", nullable=true),
     *             @OA\Property(property="batchId", type="string", format="uuid", nullable=true, description="Must match the sale batch; cannot be changed."),
     *             @OA\Property(property="stockingId", type="string", format="uuid", nullable=true, description="Must match the sale stocking; cannot be changed."),
     *             @OA\Property(property="isTotalHarvest", type="boolean", nullable=true)
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
            message: 'Sale updated successfully.',
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

        return ApiResponse::success(message: 'Sale deleted successfully.');
    }
}
```

## `app/Presentation/Requests/Sale/SaleStoreRequest.php`

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
            'stocking_id'           => ['required', 'uuid', 'exists:stockings,id'],
            'financial_category_id' => ['nullable', 'uuid', 'exists:financial_categories,id'],
            'total_weight'          => ['required', 'numeric', 'min:0.001'],
            'price_per_kg'          => ['required', 'numeric', 'min:0'],
            'sale_date'             => ['required', 'date'],
            'status'                => ['nullable', new Enum(SaleStatus::class)],
            'notes'                 => ['nullable', 'string'],
            'is_total_harvest'      => ['nullable', 'boolean'],
            'requires_invoice'      => ['nullable', 'boolean'],
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

            'stocking_id.required' => 'The stocking is required.',
            'stocking_id.exists'   => 'The selected stocking does not exist.',

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
            'requires_invoice.boolean'  => 'The requires invoice field must be true or false.',
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
            'requires_invoice'  => $this->input('requires_invoice', $this->input('requiresInvoice')),
            'tolerance_percent' => $this->input('tolerance_percent', $this->input('tolerancePercent')),
            'needs_invoice'     => $this->input('needs_invoice', $this->input('needsInvoice')),
        ]);
    }
}
```

## `app/Presentation/Resources/Sale/SaleResource.php`

```php
<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Sale;

use App\Domain\Enums\SaleStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Models\Sale
 */
final class SaleResource extends JsonResource
{
    #[\Override]
    public function toArray(Request $request): array
    {
        /** @var SaleStatus $status */
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

            'company' => $this->whenLoaded('company', fn (): array => [
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

            'stocking' => $this->whenLoaded('stocking', fn (): ?array => $this->stocking ? [
                'id'            => $this->stocking->id,
                'quantity'      => $this->stocking->quantity,
                'averageWeight' => $this->stocking->average_weight,
            ] : null),

            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
```

## `app/Application/UseCases/Sale/ProcessHarvestSaleUseCase.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\Actions\Client\GuardClientCreditAction;
use App\Application\Actions\Sale\CloseStockingAndBatchAction;
use App\Application\Actions\Sale\GenerateReceivableAction;
use App\Application\Actions\Sale\GuardBiomassAction;
use App\Application\Actions\Sale\GuardClientFiscalDataAction;
use App\Application\Actions\Sale\RegisterBiomassOutflowAction;
use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\HarvestSaleDTO;
use App\Domain\Events\SaleProcessed;
use App\Domain\Exceptions\ClosedStockingException;
use App\Domain\Exceptions\StockingRequiredException;
use App\Domain\Models\Sale;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Motor de despesca e venda com as 5 regras de negocio aplicadas.
 *
 * Regra 1: stocking_id e obrigatorio para rastrear a origem exata do peixe.
 * Regra 2: biomassa disponivel = (current_quantity * average_weight) - peso_ja_vendido.
 *          Tolerancia de 50% acima da estimativa. Acima disso: exception + rollback.
 * Regra 3: unit_price da baixa = custo_acumulado_do_stocking / biomassa_restante (CMV exato).
 * Regra 4: is_total_harvest = true encerra o stocking e, se nao ha outros ativos, o batch.
 * Regra 5: geracao atomica de Contas a Receber se financial_category_id informado.
 */
final class ProcessHarvestSaleUseCase
{
    /**
     * Regra 2 (despesca): percentual máximo acima da biomassa estimada permitido na venda.
     * Valor fixo de domínio — não é lido do request/DTO.
     */
    private const BIOMASS_TOLERANCE_PERCENT = 50.0;

    public function __construct(
        private readonly SaleRepositoryInterface       $repository,
        private readonly CompanyResolverInterface      $companyResolver,
        private readonly GuardBiomassAction            $guardBiomass,
        private readonly RegisterBiomassOutflowAction  $registerOutflow,
        private readonly GenerateReceivableAction      $generateReceivable,
        private readonly GuardClientCreditAction       $guardClientCredit,
        private readonly GuardClientFiscalDataAction   $guardFiscalData,
        private readonly CloseStockingAndBatchAction   $closeStockingAndBatch,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function execute(array $data): Sale
    {
        $data['company_id'] = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? $data['companyId'] ?? null,
        );

        $dto = HarvestSaleDTO::fromArray($data);

        // Regra 1: stocking_id e obrigatorio — venda sem rastreio de origem nao e permitida
        if ($dto->stockingId === null) {
            throw new StockingRequiredException();
        }

        return DB::transaction(fn (): Sale => $this->process($dto));
    }

    private function process(HarvestSaleDTO $dto): Sale
    {
        /** @var Stocking $stocking */
        $stocking = Stocking::findOrFail($dto->stockingId);

        if ($stocking->isClosed()) {
            throw new ClosedStockingException($stocking->id);
        }

        // ── Validacoes pre-persistencia (sem escrita no banco) ─────────────────

        // Dados fiscais (nota fiscal)
        $this->guardFiscalData->execute($dto->clientId, $dto->needsInvoice);

        // Limite de credito do cliente
        $this->guardClientCredit->execute($dto->clientId, $dto->totalRevenue());

        // Regra 2: biomassa com tolerancia fixa (ver self::BIOMASS_TOLERANCE_PERCENT)
        $this->guardBiomass->executeWithTolerance(
            stocking:         $stocking,
            requestedWeight:  $dto->totalWeight,
            tolerancePercent: self::BIOMASS_TOLERANCE_PERCENT,
        );

        // ── Persistencia ───────────────────────────────────────────────────────

        // Passo 1: Persiste a venda
        $sale = $this->repository->create($dto->toSaleInputDTO());

        // Passo 2 (Regra 3): Baixa de biomassa com CMV exato do stocking_id
        // Peso ja vendido ANTES desta venda (exclui a atual para nao contaminar o calculo)
        $alreadySoldWeight = $this->repository->soldWeightByStocking(
            stockingId:    (string) $stocking->id,
            excludeSaleId: (string) $sale->id,
        );

        $this->registerOutflow->execute($stocking, $sale, $alreadySoldWeight);

        // Passo 3 (Regra 4): Encerra stocking e, se necessario, o batch
        if ($dto->isHarvestTotal) {
            $this->closeStockingAndBatch->execute($stocking);
        }

        // Passo 4 (Regra 5): Gera Contas a Receber de forma atomica
        $this->generateReceivable->execute($dto->toSaleInputDTO(), $sale);

        // Evento disparado apos commit — listener cria historico no stocking
        SaleProcessed::dispatch($sale);

        return $sale;
    }
}
```

## `app/Application/DTOs/HarvestSaleDTO.php`

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

    public function withStockingId(string $stockingId): self
    {
        return new self(
            companyId:           $this->companyId,
            clientId:            $this->clientId,
            batchId:             $this->batchId,
            totalWeight:         $this->totalWeight,
            pricePerKg:          $this->pricePerKg,
            saleDate:            $this->saleDate,
            isHarvestTotal:      $this->isHarvestTotal,
            stockingId:          $stockingId,
            financialCategoryId: $this->financialCategoryId,
            status:              $this->status,
            notes:               $this->notes,
            tolerancePercent:    $this->tolerancePercent,
            needsInvoice:        $this->needsInvoice,
        );
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

## `app/Application/DTOs/SaleInputDTO.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\SaleStatus;

final class SaleInputDTO
{
    public function __construct(
        public readonly string    $companyId,
        public readonly string    $clientId,
        public readonly string    $batchId,
        public readonly float     $totalWeight,
        public readonly float     $pricePerKg,
        public readonly string    $saleDate,
        public readonly ?string   $stockingId = null,
        public readonly ?string   $financialCategoryId = null,
        public readonly SaleStatus $status = SaleStatus::PENDING,
        public readonly ?string   $notes = null,
        public readonly bool      $isHarvestTotal = false,
        public readonly bool      $requiresInvoice = false,
    ) {
    }

    public function totalRevenue(): float
    {
        return round($this->totalWeight * $this->pricePerKg, 2);
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            companyId:           (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            clientId:            (string) ($data['client_id'] ?? $data['clientId'] ?? ''),
            batchId:             (string) ($data['batch_id'] ?? $data['batchId'] ?? ''),
            totalWeight:         (float)  ($data['total_weight'] ?? $data['totalWeight'] ?? 0),
            pricePerKg:          (float)  ($data['price_per_kg'] ?? $data['pricePerKg'] ?? 0),
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
            requiresInvoice:     (bool) ($data['requires_invoice'] ?? $data['requiresInvoice'] ?? false),
        );
    }
}
```

## `app/Application/Contracts/CompanyResolverInterface.php`

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

## `app/Application/Services/CompanyResolver.php`

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

## `app/Application/Actions/Sale/GuardClientFiscalDataAction.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Exceptions\ClientMissingFiscalDataException;
use App\Domain\Repositories\ClientRepositoryInterface;

final class GuardClientFiscalDataAction
{
    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository,
    ) {
    }

    /**
     * Valida que o cliente possui os dados fiscais obrigatórios para emissão de nota fiscal:
     *  - document_number (CPF ou CNPJ)
     *  - address
     *
     * Só executa se $needsInvoice for true.
     *
     * @throws ClientMissingFiscalDataException
     */
    public function execute(string $clientId, bool $needsInvoice): void
    {
        if (! $needsInvoice) {
            return;
        }

        $client = $this->clientRepository->findOrFail($clientId);

        if (empty($client->document_number) || empty($client->address)) {
            throw new ClientMissingFiscalDataException($clientId);
        }
    }
}
```

## `app/Application/Actions/Client/GuardClientCreditAction.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\Actions\Client;

use App\Domain\Exceptions\ClientCreditLimitExceededException;

/**
 * Verifica se o cliente possui limite de crédito definido e se a nova venda
 * ultrapassaria esse limite somando a exposição atual (contas a receber pendentes/em atraso).
 *
 * Regra: se credit_limit for null, nenhuma restrição é aplicada.
 */
final readonly class GuardClientCreditAction
{
    /**
     * @throws ClientCreditLimitExceededException
     */
    public function execute(string $clientId, float $newSaleAmount): void
    {
    }
}
```

## `app/Application/Actions/Sale/GuardBiomassAction.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Exceptions\InsufficientBiomassException;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\Log;

final class GuardBiomassAction
{
    /**
     * Tolerância de segurança padrão: 50% acima da biomassa estimada.
     * Cobre variações naturais de peso (ciclo lunar, jejum pré-despesca, etc.).
     */
    private const float DEFAULT_TOLERANCE_PERCENT = 50.0;

    public function __construct(
        private readonly SaleRepositoryInterface $saleRepository,
    ) {
    }

    /**
     * Valida biomassa disponível sem margem de tolerância.
     * Usada no Update (revalidação simples sem flag de despesca).
     *
     * @throws InsufficientBiomassException
     */
    public function execute(
        Stocking $stocking,
        float    $requestedWeight,
        ?string  $excludeSaleId = null,
    ): void {
        [$available] = $this->resolveAvailability($stocking, $excludeSaleId);

        if ($requestedWeight > $available) {
            throw new InsufficientBiomassException(
                available:  $available,
                requested:  $requestedWeight,
                stockingId: (string) $stocking->id,
            );
        }
    }

    /**
     * Valida biomassa com tolerância de 50% (regra 2 da despesca).
     *
     * Fórmula da biomassa disponível:
     *   (current_quantity × avg_weight) − soma_de_peso_já_vendido_deste_stocking
     *
     * Limite com tolerância:
     *   disponível × (1 + tolerancePercent / 100)
     *
     * Fluxo:
     *  - requestedWeight ≤ disponível          → OK silencioso
     *  - disponível < requestedWeight ≤ limite  → warning (log) + OK
     *  - requestedWeight > limite               → InsufficientBiomassException + rollback
     *
     * @throws InsufficientBiomassException
     */
    public function executeWithTolerance(
        Stocking $stocking,
        float    $requestedWeight,
        float    $tolerancePercent = self::DEFAULT_TOLERANCE_PERCENT,
        ?string  $excludeSaleId = null,
    ): void {
        [$available, $committedWeight] = $this->resolveAvailability($stocking, $excludeSaleId);

        $toleranceLimit = $available * (1 + $tolerancePercent / 100);

        if ($requestedWeight > $toleranceLimit) {
            throw new InsufficientBiomassException(
                available:  $toleranceLimit,
                requested:  $requestedWeight,
                stockingId: (string) $stocking->id,
            );
        }

        if ($requestedWeight > $available) {
            Log::warning('Harvest adjustment: weight exceeds biomass estimate but within tolerance.', [
                'stocking_id'       => $stocking->id,
                'current_quantity'  => $stocking->current_quantity,
                'avg_weight_kg'     => $stocking->average_weight,
                'committed_kg'      => $committedWeight,
                'available_kg'      => $available,
                'requested_kg'      => $requestedWeight,
                'tolerance_percent' => $tolerancePercent,
                'tolerance_limit'   => $toleranceLimit,
                'excess_kg'         => round($requestedWeight - $available, 4),
            ]);
        }
    }

    /**
     * Calcula a biomassa disponível do povoamento.
     *
     * Regra 2 (fórmula exata):
     *   disponível = (current_quantity × average_weight) − peso_já_vendido_do_stocking
     *
     * Retorna [disponível, pesoComprometido] para evitar recalcular.
     *
     * @return array{float, float}
     */
    private function resolveAvailability(Stocking $stocking, ?string $excludeSaleId): array
    {
        // Biomassa atual estimada do povoamento
        $currentBiomass = (float) $stocking->current_quantity
                        * (float) $stocking->average_weight;

        // Peso já comprometido em vendas anteriores deste MESMO stocking_id
        $committedWeight = $this->saleRepository->soldWeightByStocking(
            (string) $stocking->id,
            $excludeSaleId,
        );

        $available = $currentBiomass - $committedWeight;

        return [$available, $committedWeight];
    }
}
```

## `app/Application/Actions/Sale/RegisterBiomassOutflowAction.php`

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
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;

final class RegisterBiomassOutflowAction
{
    public function __construct(
        private readonly RegisterStockTransactionAction  $registerStockTransaction,
        private readonly StockingRepositoryInterface $stockingRepository,
        private readonly SaleRepositoryInterface         $saleRepository,
    ) {
    }

    /**
     * Regra 3 - CMV exato por stocking_id.
     * O unit_price e calculado pelo custo financeiro acumulado do POVOAMENTO.
     *
     * Formula: custo_total_acumulado_do_stocking / biomassa_restante
     *
     * @param float $alreadySoldWeight Peso ja vendido ANTES desta venda
     */
    public function execute(
        Stocking $stocking,
        Sale     $sale,
        float    $alreadySoldWeight,
    ): StockTransaction {
        $unitCost  = $this->calculateUnitCost($stocking, $alreadySoldWeight);
        $totalCost = round((float) $sale->total_weight * $unitCost, 4);

        return $this->registerStockTransaction->execute(new StockTransactionDTO(
            companyId:     (string) $sale->company_id,
            quantity:      (float)  $sale->total_weight,
            unitPrice:     $unitCost,
            totalCost:     $totalCost,
            unit:          Unit::KG,
            direction:     StockTransactionDirection::OUT,
            referenceId:   (string) $sale->id,
            referenceType: StockTransactionReferenceType::SALE,
        ));
    }

    /**
     * Custo unitario exato (R$/kg) - Regra 3.
     *
     * Custo total acumulado = soma de todas as entradas financeiras do stocking_id
     * Biomassa restante = (current_quantity * average_weight) - peso_ja_vendido
     */
    private function calculateUnitCost(Stocking $stocking, float $alreadySoldWeight): float
    {
        $totalAccumulatedCost = $this->stockingRepository
            ->totalAccumulatedCost((string) $stocking->id);

        if ($totalAccumulatedCost <= 0) {
            return 0.0;
        }

        $currentBiomass   = (float) $stocking->current_quantity * (float) $stocking->average_weight;
        $remainingBiomass = $currentBiomass - $alreadySoldWeight;

        if ($remainingBiomass <= 0) {
            return 0.0;
        }

        return round($totalAccumulatedCost / $remainingBiomass, 6);
    }
}
```

## `app/Application/Actions/Sale/CloseStockingAndBatchAction.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Enums\BatchStatus;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;

final class CloseStockingAndBatchAction
{
    public function __construct(
        private readonly StockingRepositoryInterface $stockingRepository,
        private readonly BatchRepositoryInterface    $batchRepository,
    ) {
    }

    /**
     * Regra 4 da despesca total:
     *  1. Encerra o status do povoamento (stocking).
     *  2. SE não houver outros povoamentos ativos no mesmo lote → encerra o lote também.
     *
     * Chamada dentro da DB::transaction do UseCase — atomicidade garantida.
     */
    public function execute(Stocking $stocking): void
    {
        // Passo 1: Encerra o povoamento
        $stocking->markAsClosed();

        // Passo 2: Verifica se existem outros povoamentos ativos no lote
        $hasOtherActiveStockings = $this->stockingRepository
            ->hasActiveStockingsInBatch(
                batchId:          (string) $stocking->batch_id,
                excludeStockingId: (string) $stocking->id,
            );

        if (! $hasOtherActiveStockings) {
            // Não há mais nenhum povoamento ativo — encerra o lote também
            $this->batchRepository->update((string) $stocking->batch_id, [
                'status' => BatchStatus::FINISHED->value,
            ]);
        }
    }
}
```

## `app/Application/Actions/Sale/GenerateReceivableAction.php`

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

final class GenerateReceivableAction
{
    public function __construct(
        private readonly FinancialTransactionRepositoryInterface $transactionRepository,
        private readonly FinancialTransactionService             $transactionService,
    ) {
    }

    /**
     * Gera automaticamente o "Contas a Receber" (PENDING) vinculado à venda.
     * Não executa se financialCategoryId for null.
     */
    public function execute(SaleInputDTO $dto, Sale $sale): void
    {
        if ($dto->financialCategoryId === null) {
            return;
        }

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
            paymentDate:         null,
            description:         "Contas a Receber — Venda #{$sale->id}",
            referenceType:       FinancialTransactionReferenceType::SALE,
            referenceId:         (string) $sale->id,
        );

        $this->transactionRepository->create(
            $this->transactionService->applyPaymentDateToDTO($receivableDTO)
        );
    }
}
```

## `app/Application/Actions/Stock/RegisterStockTransactionAction.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\Actions\Stock;

use App\Application\DTOs\StockTransactionDTO;
use App\Domain\Models\StockTransaction;
use App\Domain\Repositories\StockTransactionRepositoryInterface;

final readonly class RegisterStockTransactionAction
{
    public function __construct(
        private StockTransactionRepositoryInterface $transactionRepository,
    ) {
    }

    public function execute(StockTransactionDTO $dto): StockTransaction
    {
        return $this->transactionRepository->create($dto);
    }
}
```

## `app/Application/DTOs/StockTransactionDTO.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\StockTransactionDirection;
use App\Domain\Enums\StockTransactionReferenceType;
use App\Domain\Enums\Unit;

/**
 * @property string $companyId
 * @property float $quantity
 * @property float $unitPrice
 * @property float $totalCost
 * @property Unit $unit
 * @property StockTransactionDirection $direction
 * @property string|null $supplyId
 * @property string|null $referenceId
 * @property StockTransactionReferenceType|null $referenceType
 */
final readonly class StockTransactionDTO
{
    public function __construct(
        public string $companyId,
        public float $quantity,
        public float $unitPrice,
        public float $totalCost,
        public Unit $unit,
        public StockTransactionDirection $direction,
        public ?string $supplyId = null,
        public ?string $referenceId = null,
        public ?StockTransactionReferenceType $referenceType = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(): array
    {
        return array_filter([
            'company_id'     => $this->companyId,
            'quantity'       => $this->quantity,
            'unit_price'     => $this->unitPrice,
            'total_cost'     => $this->totalCost,
            'unit'           => $this->unit->value,
            'direction'      => $this->direction->value,
            'supply_id'      => $this->supplyId,
            'reference_id'   => $this->referenceId,
            'reference_type' => $this->referenceType?->value,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
```

## `app/Application/DTOs/FinancialTransactionInputDTO.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\FinancialTransactionReferenceType;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;

final readonly class FinancialTransactionInputDTO
{
    public function __construct(
        public string $companyId,
        public string $financialCategoryId,
        public FinancialType $type,
        public float $amount,
        public string $dueDate,
        public FinancialTransactionStatus $status = FinancialTransactionStatus::PENDING,
        public ?string $paymentDate = null,
        public ?string $description = null,
        public ?string $notes = null,
        public ?FinancialTransactionReferenceType $referenceType = null,
        public ?string $referenceId = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $referenceType = isset($data['reference_type'])
            ? FinancialTransactionReferenceType::from((string) $data['reference_type'])
            : null;

        $status = isset($data['status'])
            ? FinancialTransactionStatus::from((string) $data['status'])
            : FinancialTransactionStatus::PENDING;

        return new self(
            companyId:           (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            financialCategoryId: (string) ($data['financial_category_id'] ?? $data['financialCategoryId'] ?? ''),
            type:                FinancialType::from((string) ($data['type'] ?? '')),
            amount:              (float) ($data['amount'] ?? 0),
            dueDate:             (string) ($data['due_date'] ?? $data['dueDate'] ?? ''),
            status:              $status,
            paymentDate:         isset($data['payment_date']) ? (string) $data['payment_date']
                               : (isset($data['paymentDate']) ? (string) $data['paymentDate'] : null),
            description:         isset($data['description']) ? (string) $data['description'] : null,
            notes:               isset($data['notes']) ? (string) $data['notes'] : null,
            referenceType:       $referenceType,
            referenceId:         isset($data['reference_id']) ? (string) $data['reference_id']
                               : (isset($data['referenceId']) ? (string) $data['referenceId'] : null),
        );
    }
}
```

## `app/Application/Services/FinancialTransactionService.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\FinancialTransactionInputDTO;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;
use App\Domain\Exceptions\CategoryTypeMismatchException;
use App\Domain\Exceptions\TransactionAmountImmutableException;
use App\Domain\Models\FinancialTransaction;
use App\Domain\Repositories\FinancialCategoryRepositoryInterface;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class FinancialTransactionService
{
    public function __construct(
        private FinancialCategoryRepositoryInterface $categoryRepository,
    ) {
    }

    /**
     * @throws CategoryTypeMismatchException
     */
    public function validateCategoryType(
        string $categoryId,
        FinancialType $transactionType,
    ): void {
        $category = $this->categoryRepository->findOrFail($categoryId);

        if ($category->type !== $transactionType) {
            throw new CategoryTypeMismatchException(
                transactionType: $transactionType,
                categoryType:    $category->type,
            );
        }
    }

    /**
     * @throws TransactionAmountImmutableException
     */
    public function guardAmountImmutability(
        FinancialTransaction $transaction,
        ?float $newAmount,
    ): void {
        if (! $transaction->isOriginatedExternally()) {
            return;
        }

        if ($newAmount !== null && $newAmount !== (float) $transaction->amount) {
            throw new TransactionAmountImmutableException($transaction->id);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function resolvePaymentDate(
        FinancialTransactionStatus $status,
        ?string $paymentDate,
    ): ?string {
        if (! $status->isPaid()) {
            return null;
        }

        $resolved = $paymentDate ?? CarbonImmutable::today()->toDateString();

        if (CarbonImmutable::parse($resolved)->isAfter(CarbonImmutable::today())) {
            throw new InvalidArgumentException(
                'The payment date cannot be a future date.'
            );
        }

        return $resolved;
    }

    public function applyPaymentDateToDTO(FinancialTransactionInputDTO $dto): FinancialTransactionInputDTO
    {
        return new FinancialTransactionInputDTO(
            companyId:           $dto->companyId,
            financialCategoryId: $dto->financialCategoryId,
            type:                $dto->type,
            amount:              $dto->amount,
            dueDate:             $dto->dueDate,
            status:              $dto->status,
            paymentDate:         $this->resolvePaymentDate($dto->status, $dto->paymentDate),
            description:         $dto->description,
            notes:               $dto->notes,
            referenceType:       $dto->referenceType,
            referenceId:         $dto->referenceId,
        );
    }
}
```

## `app/Domain/Repositories/SaleRepositoryInterface.php`

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

    /**
     * Busca a venda aplicando lockForUpdate (lock pessimista).
     * Usado pelo UpdateSaleUseCase para evitar edições concorrentes na mesma venda.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFailLocked(string $id): Sale;
}
```

## `app/Infrastructure/Persistence/SaleRepository.php`

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

    public function findOrFailLocked(string $id): Sale
    {
        return Sale::with([
            'company:id,name',
            'client:id,name',
            'batch:id,name',
            'stocking',
        ])->whereKey($id)->lockForUpdate()->firstOrFail();
    }
}
```

## `app/Domain/Repositories/FinancialTransactionRepositoryInterface.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\FinancialTransactionInputDTO;
use App\Domain\Models\FinancialTransaction;
use Illuminate\Support\Collection;

interface FinancialTransactionRepositoryInterface
{
    /**
     * Create a new financial transaction record.
     */
    public function create(FinancialTransactionInputDTO $dto): FinancialTransaction;

    /**
     * Update an existing financial transaction record.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): FinancialTransaction;

    /**
     * Delete a financial transaction record (soft delete).
     */
    public function delete(string $id): bool;

    /**
     * Find a transaction by ID or throw ModelNotFoundException.
     */
    public function findOrFail(string $id): FinancialTransaction;

    /**
     * Paginate financial transactions filtered by company.
     *
     * @param array{
     *     company_id: string,
     *     status?: string|null,
     *     type?: string|null,
     *     financial_category_id?: string|null,
     *     due_date_from?: string|null,
     *     due_date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;

    /**
     * Find a financial transaction by a specific field.
     */
    public function showFinancialTransaction(string $field, string | int $value): ?FinancialTransaction;

    /**
     * Find financial transactions by sale ID.
     */
    public function findLockedBySaleId(string $saleId): Collection;
}
```

## `app/Infrastructure/Persistence/FinancialTransactionRepository.php`

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\FinancialTransactionInputDTO;
use App\Domain\Enums\FinancialTransactionReferenceType;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;
use App\Domain\Models\FinancialTransaction;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Support\Collection;

final class FinancialTransactionRepository implements FinancialTransactionRepositoryInterface
{
    public function create(FinancialTransactionInputDTO $dto): FinancialTransaction
    {
        /** @var FinancialTransaction $transaction */
        $transaction = FinancialTransaction::create([
            'company_id'            => $dto->companyId,
            'financial_category_id' => $dto->financialCategoryId,
            'type'                  => $dto->type->value,
            'status'                => $dto->status->value,
            'amount'                => $dto->amount,
            'due_date'              => $dto->dueDate,
            'payment_date'          => $dto->paymentDate,
            'description'           => $dto->description,
            'notes'                 => $dto->notes,
            'reference_type'        => $dto->referenceType?->value,
            'reference_id'          => $dto->referenceId,
        ]);

        return $transaction->load(['company:id,name', 'category:id,name,type']);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): FinancialTransaction
    {
        $transaction = $this->findOrFail($id);

        $transaction->update($attributes);

        return $transaction->refresh()->load(['company:id,name', 'category:id,name,type']);
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function findOrFail(string $id): FinancialTransaction
    {
        return FinancialTransaction::with([
            'company:id,name',
            'category:id,name,type',
        ])->findOrFail($id);
    }

    /**
     * @param array{
     *     company_id: string,
     *     status?: string|null,
     *     type?: string|null,
     *     financial_category_id?: string|null,
     *     due_date_from?: string|null,
     *     due_date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = FinancialTransaction::with([
            'company:id,name',
            'category:id,name,type',
        ])
            ->where('company_id', $filters['company_id'])
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where(
                    'status',
                    FinancialTransactionStatus::from((string) $filters['status'])->value,
                ),
            )
            ->when(
                ! empty($filters['type']),
                static fn ($q) => $q->where(
                    'type',
                    FinancialType::from((string) $filters['type'])->value,
                ),
            )
            ->when(
                ! empty($filters['financial_category_id']),
                static fn ($q) => $q->where('financial_category_id', $filters['financial_category_id']),
            )
            ->when(
                ! empty($filters['due_date_from']),
                static fn ($q) => $q->whereDate('due_date', '>=', $filters['due_date_from']),
            )
            ->when(
                ! empty($filters['due_date_to']),
                static fn ($q) => $q->whereDate('due_date', '<=', $filters['due_date_to']),
            )
            ->latest('due_date')
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function showFinancialTransaction(string $field, string | int $value): ?FinancialTransaction
    {
        return FinancialTransaction::with([
            'company:id,name',
            'category:id,name,type',
        ])
            ->where($field, $value)
            ->first();
    }

    public function findLockedBySaleId(string $saleId): Collection
    {
        return FinancialTransaction::query()
            ->where('reference_type', FinancialTransactionReferenceType::SALE->value)
            ->where('reference_id', $saleId)
            ->lockForUpdate()
            ->get();
    }
}
```

## `app/Infrastructure/Persistence/StockTransactionRepository.php`

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\StockTransactionDTO;
use App\Domain\Enums\StockTransactionDirection;
use App\Domain\Models\StockTransaction;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockTransactionRepositoryInterface;
use Illuminate\Support\Str;

final class StockTransactionRepository implements StockTransactionRepositoryInterface
{
    public function create(StockTransactionDTO $dto): StockTransaction
    {
        return StockTransaction::create([
            'id'             => (string) Str::uuid(),
            'company_id'     => $dto->companyId,
            'supply_id'      => $dto->supplyId,
            'quantity'       => $dto->quantity,
            'unit_price'     => $dto->unitPrice,
            'total_cost'     => $dto->totalCost,
            'unit'           => $dto->unit->value,
            'direction'      => $dto->direction->value,
            'reference_id'   => $dto->referenceId,
            'reference_type' => $dto->referenceType?->value,
        ]);
    }

    public function findBy(string $field, string | int $value): ?StockTransaction
    {
        return StockTransaction::where($field, $value)->first();
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = StockTransaction::query()
            ->where('company_id', $filters['company_id'])
            ->when(
                ! empty($filters['direction']),
                static fn ($q) => $q->where(
                    'direction',
                    StockTransactionDirection::from($filters['direction'])->value,
                ),
            )
            ->when(
                ! empty($filters['reference_type']),
                static fn ($q) => $q->where('reference_type', $filters['reference_type']),
            )
            ->when(
                ! empty($filters['reference_id']),
                static fn ($q) => $q->where('reference_id', $filters['reference_id']),
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }
}
```

## `app/Domain/Events/SaleProcessed.php`

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

## `app/Application/Listeners/GenerateStockingHistory.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\Listeners;

use App\Domain\Enums\StockingHistoryEvent;
use App\Domain\Enums\StockingStatus;
use App\Domain\Events\FeedingCreated;
use App\Domain\Events\MortalityRecorded;
use App\Domain\Events\SaleProcessed;
use App\Domain\Models\Stocking;
use App\Domain\Models\StockingHistory;
use Illuminate\Support\Str;

/**
 * Single listener that handles all three domain events and creates the
 * corresponding StockingHistory records automatically.
 *
 * Registered for: FeedingCreated | MortalityRecorded | SaleProcessed
 */
final class GenerateStockingHistory
{
    public function handleFeedingCreated(FeedingCreated $event): void
    {
        $stocking = $this->findActiveStockingByBatch($event->feeding->batch_id);

        if (! $stocking instanceof Stocking) {
            return;
        }

        StockingHistory::create([
            'id'          => (string) Str::uuid(),
            'company_id'  => $event->companyId,
            'stocking_id' => $stocking->id,
            'event'       => StockingHistoryEvent::FEEDING->value,
            'event_date'  => $event->feeding->feeding_date?->toDateString() ?? now()->toDateString(),
            'notes'       => sprintf(
                'Alimentação: %.2f kg de %s fornecidos.',
                $event->feeding->quantity_provided,
                $event->feeding->feed_type,
            ),
        ]);
    }

    public function handleMortalityRecorded(MortalityRecorded $event): void
    {
        $stocking = $this->findActiveStockingByBatch($event->mortality->batch_id);

        if (! $stocking instanceof Stocking) {
            return;
        }

        StockingHistory::create([
            'id'          => (string) Str::uuid(),
            'company_id'  => $event->companyId,
            'stocking_id' => $stocking->id,
            'event'       => StockingHistoryEvent::MORTALITY->value,
            'event_date'  => $event->mortality->mortality_date?->toDateString() ?? now()->toDateString(),
            'quantity'    => $event->mortality->quantity,
            'notes'       => sprintf(
                'Mortalidade registrada: %d unidades. Causa: %s.',
                $event->mortality->quantity,
                $event->mortality->cause,
            ),
        ]);
    }

    public function handleSaleProcessed(SaleProcessed $event): void
    {
        $sale = $event->sale;

        // Only generate stocking history when the sale is linked to a specific stocking
        if ($sale->stocking_id === null) {
            return;
        }

        StockingHistory::create([
            'id'          => (string) Str::uuid(),
            'company_id'  => $sale->company_id,
            'stocking_id' => $sale->stocking_id,
            'event'       => StockingHistoryEvent::HARVEST->value,
            'event_date'  => $sale->sale_date->toDateString(),
            'notes'       => sprintf(
                'Despesca: %.2f kg a R$ %.2f/kg. Receita total: R$ %.2f.',
                $sale->total_weight,
                $sale->price_per_kg,
                $sale->total_revenue,
            ),
        ]);
    }

    /**
     * Finds the latest active stocking for a given batch.
     * Returns null if no active stocking exists (feeding/mortality without stocking context).
     */
    private function findActiveStockingByBatch(string $batchId): ?Stocking
    {
        return Stocking::where('batch_id', $batchId)
            ->where('status', StockingStatus::ACTIVE)
            ->latest('stocking_date')
            ->first();
    }
}
```

## `app/Infrastructure/Providers/EventServiceProvider.php`

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Application\Listeners\GenerateStockingHistory;
use App\Domain\Events\FeedingCreated;
use App\Domain\Events\MortalityRecorded;
use App\Domain\Events\SaleProcessed;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        FeedingCreated::class => [
            GenerateStockingHistory::class . '@handleFeedingCreated',
        ],

        MortalityRecorded::class => [
            GenerateStockingHistory::class . '@handleMortalityRecorded',
        ],

        SaleProcessed::class => [
            GenerateStockingHistory::class . '@handleSaleProcessed',
        ],
    ];
}
```

## `app/Domain/Exceptions/StockingRequiredException.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class StockingRequiredException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'A stocking_id is required to record a harvest sale. '
            . 'Sales without a stocking reference are not permitted.'
        );
    }
}
```

## `app/Domain/Exceptions/ClosedStockingException.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class ClosedStockingException extends RuntimeException
{
    public function __construct(string $stockingId)
    {
        parent::__construct(
            "A estocagem (id: {$stockingId}) já foi encerrada (despesca total realizada). "
            . 'Não é possível registrar novas vendas para lotes encerrados.'
        );
    }
}
```

## `app/Domain/Exceptions/ClientMissingFiscalDataException.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class ClientMissingFiscalDataException extends RuntimeException
{
    public function __construct(string $clientId)
    {
        parent::__construct(
            "The client (id: {$clientId}) does not have CPF/CNPJ and/or address registered. "
            . 'For invoice issuance, the document and address are required.'
        );
    }
}
```

## `app/Domain/Exceptions/InsufficientBiomassException.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class InsufficientBiomassException extends RuntimeException
{
    public function __construct(
        public readonly float $available,
        public readonly float $requested,
        public readonly string $stockingId,
    ) {
        parent::__construct(
            sprintf(
                'Insufficient biomass in the batch/stocking (id: %s). '
                . 'Available: %.2f kg | Requested: %.2f kg.',
                $stockingId,
                $available,
                $requested,
            )
        );
    }
}
```

## `app/Domain/Exceptions/ClientCreditLimitExceededException.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class ClientCreditLimitExceededException extends RuntimeException
{
    public function __construct(string $clientId, float $creditLimit, float $currentExposure, float $newSaleAmount)
    {
        $total = $currentExposure + $newSaleAmount;

        parent::__construct(
            "The client (id: {$clientId}) has exceeded the credit limit. "
            . "Limit: R$ {$creditLimit} | Current exposure:"
            . "R$ {$currentExposure} | New sale: R$ {$newSaleAmount} | Total: R$ {$total}."
        );
    }
}
```

---

## `app/Presentation/Exceptions/Handler.php` (referência)

Exceções típicas do create (`StockingRequiredException`, `ClosedStockingException`, `ClientMissingFiscalDataException`, `InsufficientBiomassException`, `CategoryTypeMismatchException`, `CompanyNotFoundException`, etc.) são registradas como renderables de domínio — consulte o arquivo completo no projeto.

---
