<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Supply;

use App\Domain\Enums\SupplyCategoryEnum;
use App\Domain\Enums\SupplyStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SupplyResource",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="companyId", type="string", format="uuid"),
 *     @OA\Property(property="sku", type="string", nullable=true),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="category", type="string", enum={"feed","medication","fertilizer","probiotic","equipment","packaging","finished_product","other"}),
 *     @OA\Property(property="categoryLabel", type="string"),
 *     @OA\Property(property="unit", type="string"),
 *     @OA\Property(property="unitCost", type="number", format="float"),
 *     @OA\Property(property="salePrice", type="number", format="float"),
 *     @OA\Property(property="currentStock", type="number", format="float"),
 *     @OA\Property(property="minStock", type="number", format="float"),
 *     @OA\Property(property="supplierId", type="string", format="uuid", nullable=true),
 *     @OA\Property(property="supplier", type="object", nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="contact", type="string", nullable=true),
 *         @OA\Property(property="phone", type="string", nullable=true),
 *         @OA\Property(property="email", type="string", nullable=true)
 *     ),
 *     @OA\Property(property="isProduct", type="boolean"),
 *     @OA\Property(property="status", type="string", enum={"active","inactive","low_stock"}),
 *     @OA\Property(property="statusLabel", type="string"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(
 *         property="company",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 *
 * @property-read string                           $id
 * @property-read string                           $company_id
 * @property-read string|null                      $sku
 * @property-read string                           $name
 * @property-read SupplyCategoryEnum               $category
 * @property-read string                           $unit
 * @property-read string                           $unit_cost
 * @property-read string                           $sale_price
 * @property-read string                           $current_stock
 * @property-read string                           $min_stock
 * @property-read string|null                         $supplier_id
 * @property-read \App\Domain\Models\Supplier|null    $supplier
 * @property-read bool                                $is_product
 * @property-read SupplyStatusEnum                    $status
 * @property-read string|null                      $description
 * @property-read \Illuminate\Support\Carbon|null  $created_at
 * @property-read \Illuminate\Support\Carbon|null  $updated_at
 * @property-read \App\Domain\Models\Company|null  $company
 */
final class SupplyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'companyId'     => $this->company_id,
            'sku'           => $this->sku,
            'name'          => $this->name,
            'category'      => $this->category->value,
            'categoryLabel' => $this->category->label(),
            'unit'          => $this->unit,
            'unitCost'      => (float) $this->unit_cost,
            'salePrice'     => (float) $this->sale_price,
            'currentStock'  => (float) $this->current_stock,
            'minStock'      => (float) $this->min_stock,
            'supplierId'    => $this->supplier_id,
            'supplier'      => $this->whenLoaded('supplier', fn (): array => [
                'id'      => $this->supplier->id,
                'name'    => $this->supplier->name,
                'contact' => $this->supplier->contact,
                'phone'   => $this->supplier->phone,
                'email'   => $this->supplier->email,
            ]),
            'isProduct'   => $this->is_product,
            'status'      => $this->status->value,
            'statusLabel' => $this->status->label(),
            'description' => $this->description,
            'company'     => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
