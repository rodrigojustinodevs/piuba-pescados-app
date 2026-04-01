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
 *     @OA\Property(property="contact", type="string", nullable=true),
 *     @OA\Property(property="phone", type="string", nullable=true),
 *     @OA\Property(property="email", type="string", nullable=true),
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
 * @property-read string|null                      $contact
 * @property-read string|null                      $phone
 * @property-read string|null                      $email
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
        return [
            'id'        => $this->id,
            'companyId' => $this->company_id,
            'name'      => $this->name,
            'contact'   => $this->contact,
            'phone'     => $this->phone,
            'email'     => $this->email,
            'company'   => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
