<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Enums\SupplyCategoryEnum;
use App\Domain\Enums\SupplyStatusEnum;
use App\Domain\Models\Supply;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supply>
 */
class SupplyFactory extends Factory
{
    protected $model = Supply::class;

    public function definition(): array
    {
        $currentStock = $this->faker->randomFloat(3, 0, 1000);
        $minStock     = $this->faker->randomFloat(3, 10, 200);

        $status = $currentStock <= $minStock
            ? SupplyStatusEnum::LOW_STOCK->value
            : SupplyStatusEnum::ACTIVE->value;

        return [
            'sku'           => strtoupper($this->faker->bothify('SUP-####')),
            'name'          => $this->faker->words(3, true),
            'category'      => $this->faker->randomElement(SupplyCategoryEnum::cases())->value,
            'unit'          => $this->faker->randomElement(['kg', 'L', 'unit', 'bag', 'g', 'ml']),
            'unit_cost'     => $this->faker->randomFloat(2, 1, 500),
            'sale_price'    => $this->faker->randomFloat(2, 0, 1000),
            'current_stock' => $currentStock,
            'min_stock'     => $minStock,
            'supplier_id'   => null,
            'is_product'    => $this->faker->boolean(30),
            'status'        => $status,
            'description'   => $this->faker->optional(0.5)->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status'        => SupplyStatusEnum::ACTIVE->value,
            'current_stock' => 500,
            'min_stock'     => 100,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (): array => [
            'status'        => SupplyStatusEnum::LOW_STOCK->value,
            'current_stock' => 10,
            'min_stock'     => 100,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => SupplyStatusEnum::INACTIVE->value,
        ]);
    }

    public function asProduct(): static
    {
        return $this->state(fn (): array => [
            'is_product' => true,
            'sale_price' => $this->faker->randomFloat(2, 10, 500),
        ]);
    }
}
