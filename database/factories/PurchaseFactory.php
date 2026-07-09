<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Enums\PurchasePaymentStatus;
use App\Domain\Enums\PurchaseStatus;
use App\Domain\Models\Company;
use App\Domain\Models\Purchase;
use App\Domain\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        return [
            'code'           => strtoupper($this->faker->bothify('PUR-####')),
            'company_id'     => Company::factory(),
            'supplier_id'    => Supplier::factory(),
            'order_date'     => now(),
            'status'         => PurchaseStatus::DRAFT->value,
            'payment_status' => PurchasePaymentStatus::PENDING->value,
            'total_price'    => $this->faker->randomFloat(2, 100, 5000),
            'freight'        => 0,
            'other_costs'    => 0,
        ];
    }
}
