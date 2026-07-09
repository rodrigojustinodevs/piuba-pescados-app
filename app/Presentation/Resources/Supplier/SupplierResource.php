<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SupplierResource",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="companyId", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="tradeName", type="string", nullable=true),
 *     @OA\Property(property="document", type="string", nullable=true),
 *     @OA\Property(property="stateRegistration", type="string", nullable=true),
 *     @OA\Property(property="email", type="string", nullable=true),
 *     @OA\Property(property="phone", type="string", nullable=true),
 *     @OA\Property(property="contactName", type="string", nullable=true),
 *     @OA\Property(property="category", type="string",
 *         enum={"feed","medication","equipment","supply","service","logistics","other"}),
 *     @OA\Property(property="paymentTerms", type="string", nullable=true),
 *     @OA\Property(property="rating", type="number", format="float"),
 *     @OA\Property(
 *         property="address",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="street", type="string", nullable=true),
 *         @OA\Property(property="number", type="string", nullable=true),
 *         @OA\Property(property="complement", type="string", nullable=true),
 *         @OA\Property(property="neighborhood", type="string", nullable=true),
 *         @OA\Property(property="city", type="string", nullable=true),
 *         @OA\Property(property="state", type="string", nullable=true),
 *         @OA\Property(property="zipCode", type="string", nullable=true)
 *     ),
 *     @OA\Property(property="status", type="string", enum={"active","inactive","suspended"}),
 *     @OA\Property(property="totalPurchases", type="integer"),
 *     @OA\Property(property="lastPurchaseAt", type="string", format="date-time", nullable=true),
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
 * @property-read string                           $name
 * @property-read string|null                      $trade_name
 * @property-read string|null                      $contact
 * @property-read string|null                      $phone
 * @property-read string|null                      $email
 * @property-read string|null                      $document
 * @property-read string|null                      $state_registration
 * @property-read \App\Domain\Enums\SupplierCategoryEnum $category
 * @property-read string|null                      $payment_terms
 * @property-read float                             $rating
 * @property-read array<string, mixed>|null         $address
 * @property-read \App\Domain\Enums\SupplierStatusEnum   $status
 * @property-read int|null                          $total_purchases
 * @property-read string|null                       $purchases_max_order_date
 * @property-read \Illuminate\Support\Carbon|null  $created_at
 * @property-read \Illuminate\Support\Carbon|null  $updated_at
 * @property-read \App\Domain\Models\Company|null  $company
 */
final class SupplierResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        $address = $this->address;

        return [
            'id'                => $this->id,
            'companyId'         => $this->company_id,
            'name'              => $this->name,
            'tradeName'         => $this->trade_name,
            'document'          => $this->document,
            'stateRegistration' => $this->state_registration,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'contactName'       => $this->contact,
            'category'          => $this->category->value,
            'paymentTerms'      => $this->payment_terms,
            'rating'            => (float) $this->rating,
            'address'           => $address === null ? null : [
                'street'       => $address['street'] ?? null,
                'number'       => $address['number'] ?? null,
                'complement'   => $address['complement'] ?? null,
                'neighborhood' => $address['neighborhood'] ?? null,
                'city'         => $address['city'] ?? null,
                'state'        => $address['state'] ?? null,
                'zipCode'      => $address['zip_code'] ?? null,
            ],
            'status'         => $this->status->value,
            'totalPurchases' => (int) ($this->total_purchases ?? 0),
            'lastPurchaseAt' => $this->purchases_max_order_date === null
                ? null
                : \Illuminate\Support\Carbon::parse($this->purchases_max_order_date)->toIso8601String(),
            'company' => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
