<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Enums\SupplierCategoryEnum;
use App\Domain\Enums\SupplierStatusEnum;
use App\Domain\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name'               => $this->faker->company(),
            'trade_name'         => $this->faker->companySuffix(),
            'contact'            => $this->faker->name(),
            'phone'              => $this->faker->numerify('(##) #####-####'),
            'email'              => $this->faker->companyEmail(),
            'document'           => $this->faker->numerify('##############'),
            'state_registration' => $this->faker->numerify('#########'),
            'category'           => $this->faker->randomElement(SupplierCategoryEnum::cases())->value,
            'payment_terms'      => '30 dias',
            'rating'             => $this->faker->randomFloat(1, 0, 5),
            'address'            => [
                'street'       => $this->faker->streetName(),
                'number'       => (string) $this->faker->buildingNumber(),
                'complement'   => null,
                'neighborhood' => $this->faker->citySuffix(),
                'city'         => $this->faker->city(),
                'state'        => $this->faker->stateAbbr(),
                'zip_code'     => $this->faker->postcode(),
            ],
            'status' => SupplierStatusEnum::ACTIVE->value,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['status' => SupplierStatusEnum::INACTIVE->value]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => ['status' => SupplierStatusEnum::SUSPENDED->value]);
    }
}
