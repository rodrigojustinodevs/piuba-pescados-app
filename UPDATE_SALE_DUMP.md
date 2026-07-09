# UpdateSale — dump completo (estado atual do repositório)

Fluxo **ativo**: `PUT /company/sale/{id}` → `SaleController::update` → `UpdateSaleUseCase` (transação única) → `SaleRepository::findOrFailLocked`, biomassa, ciclo de vida (despesca total), `SyncReceivableAmountAction`, persistência.

**Nota:** `UpdateSaleAction` existe no código mas **não** é injetado no controller; a lógica de edição está em `UpdateSaleUseCase`. O PHPDoc do controller ainda menciona a Action — convém alinhar a documentação depois.

---
## Índice — fluxo atual

- `routes/app/company/sale.php`
- `app/Presentation/Controllers/SaleController.php`
- `app/Presentation/Requests/Sale/SaleUpdateRequest.php`
- `app/Presentation/Resources/Sale/SaleResource.php`
- `app/Application/UseCases/Sale/UpdateSaleUseCase.php`
- `app/Application/Actions/Sale/SyncReceivableAmountAction.php`
- `app/Application/Actions/Sale/ReopenStockingAndBatchAction.php`
- `app/Application/Actions/Sale/CloseStockingAndBatchAction.php`
- `app/Application/Actions/Sale/GuardBiomassAction.php`
- `app/Domain/Repositories/SaleRepositoryInterface.php`
- `app/Infrastructure/Persistence/SaleRepository.php`
- `app/Domain/Repositories/StockingRepositoryInterface.php`
- `app/Infrastructure/Persistence/StockingRepository.php`
- `app/Domain/Exceptions/SaleFinanciallyLockedException.php`
- `app/Domain/Exceptions/InsufficientBiomassException.php`
- `app/Application/Actions/Sale/UpdateSaleAction.php` *(implementação alternativa / não usada pelo controller)*

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

## `app/Presentation/Requests/Sale/SaleUpdateRequest.php`

```php
<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Sale;

use App\Domain\Enums\SaleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // ✅ Padrão correto: só normaliza se o campo camelCase existe e snake_case não
        // O problema original: input('field', input('camelCase')) injeta null
        // quando nenhum dos dois existe, sobrescrevendo campos ausentes silenciosamente.
        $map = [
            'clientId'       => 'client_id',
            'batchId'        => 'batch_id',
            'stockingId'     => 'stocking_id',
            'totalWeight'    => 'total_weight',
            'pricePerKg'     => 'price_per_kg',
            'saleDate'       => 'sale_date',
            'isTotalHarvest' => 'is_total_harvest',
        ];

        $normalized = [];
        foreach ($map as $camel => $snake) {
            if ($this->has($camel) && ! $this->has($snake)) {
                $normalized[$snake] = $this->input($camel);
            }
        }

        if ($normalized) {
            $this->merge($normalized);
        }
    }

    public function rules(): array
    {
        return [
            'total_weight'     => ['sometimes', 'numeric', 'min:0.001'],
            'price_per_kg'     => ['sometimes', 'numeric', 'min:0'],
            'sale_date'        => ['sometimes', 'date'],
            'status'           => ['sometimes', Rule::enum(SaleStatus::class)],
            'notes'            => ['sometimes', 'nullable', 'string'],
            'is_total_harvest' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'total_weight.min'    => 'The total weight must be greater than zero.',
            'price_per_kg.min'    => 'The price per kg must be greater than zero.',
            'sale_date.date'      => 'The sale date must be a valid date.',
            'status.enum'         => 'The status must be: pending, confirmed or cancelled.',
        ];
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

## `app/Application/UseCases/Sale/UpdateSaleUseCase.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\Actions\Sale\CloseStockingAndBatchAction;
use App\Application\Actions\Sale\GuardBiomassAction;
use App\Application\Actions\Sale\ReopenStockingAndBatchAction;
use App\Application\Actions\Sale\SyncReceivableAmountAction;
use App\Domain\Enums\SaleStatus;
use App\Domain\Models\Sale;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class UpdateSaleUseCase
{
    public function __construct(
        private readonly SaleRepositoryInterface      $saleRepository,
        private readonly StockingRepositoryInterface  $stockingRepository,
        private readonly GuardBiomassAction           $guardBiomass,
        private readonly CloseStockingAndBatchAction  $closeStockingAndBatch,
        private readonly ReopenStockingAndBatchAction $reopenStockingAndBatch,
        private readonly SyncReceivableAmountAction   $syncReceivable,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(string $id, array $data): Sale
    {
        return DB::transaction(function () use ($id, $data): Sale {
            
            $sale = $this->saleRepository->findOrFailLocked($id);

            $attributes = $this->buildAttributes($sale, $data);

            // ── Validação de biomassa ─────────────────────────────────────────
            $stocking = $this->resolveLockedStocking($sale);

            $newWeight = isset($attributes['total_weight'])
                ? (float) $attributes['total_weight']
                : (float) $sale->total_weight;

            if ($stocking !== null && $newWeight > (float) $sale->total_weight) {
                // Revalida biomassa excluindo o peso desta própria venda
                $this->guardBiomass->execute($stocking, $newWeight, $sale->id);
            }

            // ── Ciclo de vida do stocking/batch ───────────────────────────────
            $this->applyHarvestLifecycle($sale, $stocking, $attributes);

            // ── Sincronização do recebível ────────────────────────────────────
            $newRevenue = $this->resolveNewRevenue($sale, $attributes);
            $oldRevenue = round((float) $sale->total_revenue, 2);

            $this->syncReceivable->execute($sale->id, $newRevenue, $oldRevenue);

            if ($newRevenue !== $oldRevenue) {
                $attributes['total_revenue'] = $newRevenue;
            }

            // ── Persistência ──────────────────────────────────────────────────
            if ($attributes === []) {
                return $this->saleRepository->findOrFail($id);
            }

            return $this->saleRepository->update($id, $attributes);
        });
    }

    // ── Privados ──────────────────────────────────────────────────────────────

    /**
     * Aplica as transições de ciclo de vida do stocking:
     *  - era total harvest → deixou de ser → reabre stocking/batch
     *  - não era total harvest → passou a ser → fecha stocking/batch
     */
    private function applyHarvestLifecycle(
        Sale     $sale,
        ?Stocking $stocking,
        array    $attributes,
    ): void {
        if ($stocking === null) {
            return;
        }

        $oldHarvest = (bool) $sale->is_total_harvest;
        $newHarvest = array_key_exists('is_total_harvest', $attributes)
            ? (bool) $attributes['is_total_harvest']
            : $oldHarvest;

        if ($oldHarvest && ! $newHarvest) {
            // Reversão: reabre stocking e batch
            $this->reopenStockingAndBatch->execute($stocking, (string) $sale->batch_id);
            return;
        }

        if (! $oldHarvest && $newHarvest && ! $stocking->isClosed()) {
            // Despesca total: fecha stocking e batch
            $this->closeStockingAndBatch->execute($stocking);
        }
    }

    /**
     * Busca e tranca o stocking via lockForUpdate para evitar race conditions.
     * Usa o repositório — nunca Stocking::query() diretamente no UseCase.
     */
    private function resolveLockedStocking(Sale $sale): ?Stocking
    {
        if ($sale->stocking_id === null) {
            return null;
        }

        return $this->stockingRepository->findOrFailLocked((string) $sale->stocking_id);
    }

    private function resolveNewRevenue(Sale $sale, array $attributes): float
    {
        $weight = isset($attributes['total_weight'])
            ? (float) $attributes['total_weight']
            : (float) $sale->total_weight;

        $price = isset($attributes['price_per_kg'])
            ? (float) $attributes['price_per_kg']
            : (float) $sale->price_per_kg;

        return round($weight * $price, 2);
    }

    /**
     * Monta o array de atributos para update a partir do patch recebido.
     * Campos ausentes em $data não sobrescrevem o estado atual da venda.
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
            $attributes['total_weight'] = (float) $data['total_weight'];
        }

        if (array_key_exists('price_per_kg', $data)) {
            $attributes['price_per_kg'] = (float) $data['price_per_kg'];
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

        if (array_key_exists('is_total_harvest', $data)) {
            $attributes['is_total_harvest'] = (bool) $data['is_total_harvest'];
        }

        return $attributes;
    }
}
```

## `app/Application/Actions/Sale/SyncReceivableAmountAction.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Enums\FinancialTransactionReferenceType;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Exceptions\SaleFinanciallyLockedException;
use App\Domain\Models\FinancialTransaction;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;
use Illuminate\Support\Collection;

final class SyncReceivableAmountAction
{
    public function __construct(
        private readonly FinancialTransactionRepositoryInterface $transactionRepository,
    ) {}

    /**
     * Busca todas as transações financeiras vinculadas à venda e, se o novo valor
     * difere do atual, atualiza o amount de cada uma.
     *
     * Trava financeira: se qualquer transação já estiver paga/vencida,
     * bloqueia a edição do valor (SaleFinanciallyLockedException).
     *
     * Só executa se houve mudança real de valor (evita writes desnecessários).
     *
     * @throws SaleFinanciallyLockedException
     */
    public function execute(string $saleId, float $newRevenue, float $oldRevenue): void
    {
        $transactions = $this->fetchLockedTransactions($saleId);

        if ($transactions->isEmpty()) {
            return;
        }

        $this->assertAllPending($transactions);

        // Só escreve se o valor mudou — evita UPDATE sem necessidade
        if (abs($newRevenue - $oldRevenue) <= 0.000_01) {
            return;
        }

        foreach ($transactions as $tx) {
            $this->transactionRepository->update((string) $tx->id, [
                'amount' => $newRevenue,
            ]);
        }
    }

    /**
     * @return Collection<int, FinancialTransaction>
     */
    private function fetchLockedTransactions(string $saleId): Collection
    {
        return FinancialTransaction::query()
            ->where('reference_type', FinancialTransactionReferenceType::SALE->value)
            ->where('reference_id', $saleId)
            ->lockForUpdate()
            ->get();
    }

    /**
     * @param Collection<int, FinancialTransaction> $transactions
     *
     * @throws SaleFinanciallyLockedException
     */
    private function assertAllPending(Collection $transactions): void
    {
        foreach ($transactions as $tx) {
            if ($tx->status !== FinancialTransactionStatus::PENDING) {
                throw new SaleFinanciallyLockedException();
            }
        }
    }
}
```

## `app/Application/Actions/Sale/ReopenStockingAndBatchAction.php`

```php
<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Enums\BatchStatus;
use App\Domain\Models\Batch;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use App\Domain\Repositories\TankRepositoryInterface;

final class ReopenStockingAndBatchAction
{
    public function __construct(
        private readonly StockingRepositoryInterface $stockingRepository,
        private readonly BatchRepositoryInterface    $batchRepository,
        private readonly TankRepositoryInterface     $tankRepository,
    ) {}

    /**
     * Reverte o encerramento de um despesca total:
     *  1. Reabre o stocking (status → active, cleared_at → null)
     *  2. Se o batch estava encerrado por causa deste stocking → reabre o batch
     *  3. Se o batch foi reaberto → reabre o tank associado
     *
     * Chamado dentro da DB::transaction do UpdateSaleUseCase — atomicidade garantida.
     */
    public function execute(Stocking $stocking, string $batchId): void
    {
        // Passo 1: Reabre o stocking
        $this->stockingRepository->update((string) $stocking->id, [
            'status'    => 'active',
            'closed_at' => null,
        ]);

        // Passo 2: Reabre o batch se estava encerrado
        /** @var Batch $batch */
        $batch = $this->batchRepository->findOrFail($batchId);

        if ($batch->isFinished()) {
            $this->batchRepository->update($batchId, [
                'status' => BatchStatus::ACTIVE->value,
            ]);

            // Passo 3: Reabre o tank associado ao batch
            $this->tankRepository->update((string) $batch->tank_id, [
                'status' => 'active',
            ]);
        }
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

## `app/Domain/Repositories/StockingRepositoryInterface.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\StockingInputDTO;
use App\Domain\Models\Stocking;

interface StockingRepositoryInterface
{
    /**
     * @param array{
     *     batch_id?: string|null,
     *     company_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     status?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface;

    /**
     * Find a stocking by ID.
     */
    public function findOrFail(string $id): Stocking;

    /**
     * Create a new stocking record.
     */
    public function create(StockingInputDTO $dto): Stocking;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Stocking;

    /**
     * Delete a stocking record.
     */
    public function delete(string $id): bool;

    /**
     * Find a stocking by a specific field.
     */
    public function showStocking(string $field, string | int $value): ?Stocking;

    /**
     * Adds amounts to accumulated_fixed_cost for multiple stockings in ONE query.
     *
     * @param array<string, float> $amountsByStockingId  stocking_id → amount to add
     */
    public function bulkIncrementFixedCost(array $amountsByStockingId): void;

    /**
     * Subtracts amounts from accumulated_fixed_cost for multiple stockings in ONE query.
     * The column is floored at 0 to prevent negative values caused by floating-point drift.
     *
     * @param array<string, float> $amountsByStockingId  stocking_id → amount to subtract
     */
    public function bulkDecrementFixedCost(array $amountsByStockingId): void;

    /**
     * Find a stocking by company ID.
     */
    public function findByCompanyOrFail(string $stockingId, string $companyId): Stocking;

    /**
     * Find a stocking by batch ID.
     */
    public function findByBatchId(string $batchId): ?Stocking;

    /**
     * Check if there are any active stockings in a batch.
     */
    public function hasActiveStockingsInBatch(string $batchId, string $excludeStockingId = null): bool;

    /**
     * Get the total accumulated cost of a batch.
     */
    public function totalAccumulatedCost(string $stockingId): float;
 
    /**
     * Busca o stocking aplicando lockForUpdate (lock pessimista).
     * Usado pelo UpdateSaleUseCase para evitar edições concorrentes no mesmo stocking.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFailLocked(string $id): Stocking;
}
```

## `app/Infrastructure/Persistence/StockingRepository.php`

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\StockingInputDTO;
use App\Domain\Enums\StockingStatus;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class StockingRepository implements StockingRepositoryInterface
{
    private const array DEFAULT_RELATIONS = [
        'batch:id,name,tank_id',
    ];

    /**
     * @param array{
     *     batch_id?: string|null,
     *     company_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     status?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface
    {
        $paginator = Stocking::with(self::DEFAULT_RELATIONS)
            ->when(
                ! empty($filters['batch_id']),
                static fn ($q) => $q->where('batch_id', $filters['batch_id']),
            )
            ->when(
                ! empty($filters['company_id']),
                static function ($q) use ($filters): void {
                    $q->whereHas(
                        'batch.tank',
                        static fn ($tq) => $tq->where('company_id', $filters['company_id']),
                    );
                },
            )
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where('status', $filters['status']),
            )
            ->when(
                ! empty($filters['date_from']),
                static fn ($q) => $q->whereDate('stocking_date', '>=', $filters['date_from']),
            )
            ->when(
                ! empty($filters['date_to']),
                static fn ($q) => $q->whereDate('stocking_date', '<=', $filters['date_to']),
            )
            ->latest('stocking_date')
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function findOrFail(string $id): Stocking
    {
        return Stocking::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }

    public function showStocking(string $field, string | int $value): ?Stocking
    {
        return Stocking::with(self::DEFAULT_RELATIONS)->where($field, $value)->first();
    }

    public function create(StockingInputDTO $dto): Stocking
    {
        /** @var Stocking $stocking */
        $stocking = Stocking::create($dto->toPersistence());

        return $stocking->load(self::DEFAULT_RELATIONS);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Stocking
    {
        $stocking = $this->findOrFail($id);
        $stocking->update($attributes);

        return $stocking->refresh();
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function bulkIncrementFixedCost(array $amountsByStockingId): void
    {
        if ($amountsByStockingId === []) {
            return;
        }

        $this->bulkAdjustFixedCost($amountsByStockingId, '+');
    }

    public function bulkDecrementFixedCost(array $amountsByStockingId): void
    {
        if ($amountsByStockingId === []) {
            return;
        }

        $this->bulkAdjustFixedCost($amountsByStockingId, '-');
    }

    /**
     * Builds and executes a single CASE WHEN UPDATE for all stockings.
     *
     * @param array<string, float> $amountsByStockingId
     * @param '+'|'-'              $operator
     */
    private function bulkAdjustFixedCost(array $amountsByStockingId, string $operator): void
    {
        $caseSql      = 'CASE id';
        $caseBindings = [];

        foreach ($amountsByStockingId as $stockingId => $amount) {
            $caseSql .= ' WHEN ? THEN accumulated_fixed_cost ' . $operator . ' ?';
            $caseBindings[] = $stockingId;
            $caseBindings[] = abs($amount);
        }

        $caseSql .= ' END';

        $ids      = array_keys($amountsByStockingId);
        $inSql    = implode(', ', array_fill(0, count($ids), '?'));
        $bindings = array_merge($caseBindings, $ids);

        DB::statement(
            "UPDATE stockings SET accumulated_fixed_cost = GREATEST(0, {$caseSql}) WHERE id IN ({$inSql})",
            $bindings,
        );
    }

    public function findByCompanyOrFail(string $stockingId, string $companyId): Stocking
    {
        /** @var Stocking */
        return Stocking::where('id', $stockingId)
            ->whereHas(
                'batch.tank',
                static fn ($q) => $q->where('company_id', $companyId),
            )
            ->firstOrFail();
    }

    public function findByBatchId(string $batchId): ?Stocking
    {
        return Stocking::with(self::DEFAULT_RELATIONS)
            ->where('batch_id', $batchId)
            ->where('status', StockingStatus::ACTIVE)
            ->latest('stocking_date')
            ->first();
    }

    public function hasActiveStockingsInBatch(string $batchId, string $excludeStockingId = null): bool
    {
        return Stocking::with(self::DEFAULT_RELATIONS)
            ->where('batch_id', $batchId)
            ->where('status', StockingStatus::ACTIVE)
            ->when($excludeStockingId, static function ($query, string $id): void {
                $query->where('id', '!=', $id);
            })
            ->exists();
    }

    public function totalAccumulatedCost(string $stockingId): float
    {
        return (float) Stocking::with(self::DEFAULT_RELATIONS)
            ->where('id', $stockingId)
            ->sum('accumulated_fixed_cost');
    }

    public function findOrFailLocked(string $id): Stocking
    {
        return Stocking::with(self::DEFAULT_RELATIONS)
            ->where('id', $id)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
```

## `app/Domain/Exceptions/SaleFinanciallyLockedException.php`

```php
<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class SaleFinanciallyLockedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'It is not possible to edit the values of a sale with registered receipts.',
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

---

## `app/Application/Actions/Sale/UpdateSaleAction.php` *(código paralelo — não referenciado por `UpdateSaleUseCase`)*

```php
<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Enums\BatchStatus;
use App\Domain\Enums\FinancialTransactionReferenceType;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\SaleStatus;
use App\Domain\Enums\StockingStatus;
use App\Domain\Exceptions\SaleFinanciallyLockedException;
use App\Domain\Models\Batch;
use App\Domain\Models\FinancialTransaction;
use App\Domain\Models\Sale;
use App\Domain\Models\Stocking;
use App\Domain\Models\Tank;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use App\Domain\Repositories\TankRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class UpdateSaleAction
{
    public function __construct(
        private readonly SaleRepositoryInterface $saleRepository,
        private readonly GuardBiomassAction $guardBiomass,
        private readonly CloseStockingAndBatchAction $closeStockingAndBatch,
        private readonly StockingRepositoryInterface $stockingRepository,
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly TankRepositoryInterface $tankRepository,
        private readonly FinancialTransactionRepositoryInterface $financialTransactionRepository,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function execute(string $id, array $data): Sale
    {
        return DB::transaction(function () use ($id, $data): Sale {
            /** @var Sale $sale */
            $sale = Sale::query()->whereKey($id)->lockForUpdate()->firstOrFail();

            /** @var Collection<int, FinancialTransaction> $financialTransactions */
            $financialTransactions = FinancialTransaction::query()
                ->where('reference_type', FinancialTransactionReferenceType::SALE->value)
                ->where('reference_id', $sale->id)
                ->lockForUpdate()
                ->get();

            $this->assertAllFinancialTitlesPending($financialTransactions);

            $stocking = null;

            if ($sale->stocking_id !== null) {
                /** @var Stocking $stocking */
                $stocking = Stocking::query()->whereKey($sale->stocking_id)->lockForUpdate()->firstOrFail();
            }

            $attributes = $this->buildAttributes($sale, $data);

            $oldWeight = (float) $sale->total_weight;
            $newWeight = isset($attributes['total_weight'])
                ? (float) $attributes['total_weight']
                : $oldWeight;

            if ($stocking instanceof Stocking && $newWeight > $oldWeight) {
                $this->guardBiomass->execute($stocking, $newWeight, $sale->id);
            }

            $oldHarvest = (bool) $sale->is_total_harvest;
            $newHarvest = array_key_exists('is_total_harvest', $attributes)
                ? (bool) $attributes['is_total_harvest']
                : $oldHarvest;

            if ($stocking instanceof Stocking) {
                if ($oldHarvest && ! $newHarvest) {
                    $this->revertTotalHarvest($sale, $stocking);
                } elseif (! $oldHarvest && $newHarvest && ! $stocking->isClosed()) {
                    $this->closeStockingAndBatch->execute($stocking);
                    $stocking->refresh();
                }
            }

            $newRevenue = $this->resolveTotalRevenue($sale, $attributes);
            $oldRevenue = round((float) $sale->total_revenue, 2);

            if ($financialTransactions->isNotEmpty()
                && abs($newRevenue - $oldRevenue) > 0.000_01) {
                foreach ($financialTransactions as $tx) {
                    $this->financialTransactionRepository->update((string) $tx->id, [
                        'amount' => $newRevenue,
                    ]);
                }
            }

            if ($attributes === []) {
                return $this->saleRepository->findOrFail($id);
            }

            return $this->saleRepository->update($id, $attributes);
        });
    }

    /**
     * @param Collection<int, FinancialTransaction> $transactions
     */
    private function assertAllFinancialTitlesPending(Collection $transactions): void
    {
        foreach ($transactions as $tx) {
            if ($tx->status !== FinancialTransactionStatus::PENDING) {
                throw new SaleFinanciallyLockedException();
            }
        }
    }

    private function revertTotalHarvest(Sale $sale, Stocking $stocking): void
    {
        $this->stockingRepository->update((string) $stocking->id, [
            'status'    => StockingStatus::ACTIVE->value,
            'closed_at' => null,
        ]);
        $stocking->refresh();

        /** @var Batch $batch */
        $batch = Batch::query()->whereKey($sale->batch_id)->lockForUpdate()->firstOrFail();

        if ($batch->isFinished()) {
            $this->batchRepository->update((string) $batch->id, [
                'status' => BatchStatus::ACTIVE->value,
            ]);
        }

        Tank::query()->whereKey($batch->tank_id)->lockForUpdate()->firstOrFail();

        $this->tankRepository->update((string) $batch->tank_id, [
            'status' => 'active',
        ]);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function resolveTotalRevenue(Sale $sale, array $attributes): float
    {
        $weight = isset($attributes['total_weight'])
            ? (float) $attributes['total_weight']
            : (float) $sale->total_weight;
        $price = isset($attributes['price_per_kg'])
            ? (float) $attributes['price_per_kg']
            : (float) $sale->price_per_kg;

        return round($weight * $price, 2);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function buildAttributes(Sale $sale, array $data): array
    {
        $attributes = [];

        if (array_key_exists('client_id', $data)) {
            $attributes['client_id'] = $data['client_id'];
        }

        if (array_key_exists('total_weight', $data)) {
            $attributes['total_weight'] = (float) $data['total_weight'];
        }

        if (array_key_exists('price_per_kg', $data)) {
            $attributes['price_per_kg'] = (float) $data['price_per_kg'];
        }

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

        if (array_key_exists('is_total_harvest', $data)) {
            $attributes['is_total_harvest'] = (bool) $data['is_total_harvest'];
        }

        return $attributes;
    }
}
```

---

## `app/Presentation/Exceptions/Handler.php` (trechos)

`SaleFinanciallyLockedException` e `InsufficientBiomassException` entram na lista de exceções de domínio e têm `renderable` com `HTTP_UNPROCESSABLE_ENTITY` (422), via `handleDomainException`.

---
