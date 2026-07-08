<?php

declare(strict_types=1);

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Enums\SupplyCategoryEnum;
use App\Domain\Enums\SupplyStatusEnum;
use App\Domain\Models\Company;
use App\Domain\Models\Supply;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $company = Company::factory()->create();

    $this->company = $company;

    // Bind companyResolver to always return this company
    $this->app->bind(CompanyResolverInterface::class, fn (): CompanyResolverInterface => new readonly class ($company->id) implements CompanyResolverInterface
    {
        public function __construct(private string $id)
        {
        }

        public function resolve(?string $companyId = null): string
        {
            return $this->id;
        }
    });
});

describe('POST /api/company/supply', function (): void {
    it('creates a supply and auto-sets active status when stock is sufficient', function (): void {
        $payload = [
            'name'          => 'Ração Inicial',
            'category'      => SupplyCategoryEnum::FEED->value,
            'unit'          => 'kg',
            'unit_cost'     => 3.50,
            'sale_price'    => 0,
            'current_stock' => 500,
            'min_stock'     => 100,
            'supplier'      => 'Nutrópica',
            'is_product'    => false,
        ];

        $response = $this->postJson('/api/company/supply', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('response.status', SupplyStatusEnum::ACTIVE->value);
        $response->assertJsonPath('response.name', 'Ração Inicial');
    });

    it('auto-sets low_stock status when current_stock <= min_stock', function (): void {
        $payload = [
            'name'          => 'Ração Engorda',
            'category'      => SupplyCategoryEnum::FEED->value,
            'unit'          => 'kg',
            'unit_cost'     => 4.00,
            'sale_price'    => 0,
            'current_stock' => 50,
            'min_stock'     => 100,
            'supplier'      => 'Nutrópica',
            'is_product'    => false,
        ];

        $response = $this->postJson('/api/company/supply', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('response.status', SupplyStatusEnum::LOW_STOCK->value);
    });

    it('rejects invalid category', function (): void {
        $payload = [
            'name'          => 'Insumo X',
            'category'      => 'invalid_category',
            'unit'          => 'kg',
            'unit_cost'     => 1,
            'sale_price'    => 0,
            'current_stock' => 100,
            'min_stock'     => 10,
            'is_product'    => false,
        ];

        $response = $this->postJson('/api/company/supply', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category']);
    });

    it('rejects negative unit_cost', function (): void {
        $payload = [
            'name'          => 'Insumo X',
            'category'      => 'feed',
            'unit'          => 'kg',
            'unit_cost'     => -1,
            'sale_price'    => 0,
            'current_stock' => 100,
            'min_stock'     => 10,
            'is_product'    => false,
        ];

        $response = $this->postJson('/api/company/supply', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['unit_cost']);
    });
});

describe('PUT /api/company/supply/{id}', function (): void {
    it('updates supply and recalculates status to low_stock', function (): void {
        /** @var Supply $supply */
        $supply = Supply::factory()->for($this->company)->active()->create();

        $response = $this->putJson("/api/company/supply/{$supply->id}", [
            'current_stock' => 5,
            'min_stock'     => 100,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('response.status', SupplyStatusEnum::LOW_STOCK->value);
    });

    it('does not change inactive supply status on stock update', function (): void {
        /** @var Supply $supply */
        $supply = Supply::factory()->for($this->company)->inactive()->create();

        $response = $this->putJson("/api/company/supply/{$supply->id}", [
            'current_stock' => 5,
            'min_stock'     => 100,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('response.status', SupplyStatusEnum::INACTIVE->value);
    });
});

describe('GET /api/company/supplies', function (): void {
    it('lists supplies with pagination', function (): void {
        Supply::factory()->for($this->company)->count(3)->create();

        $response = $this->getJson('/api/company/supplies');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'response'   => [['id', 'name', 'category', 'status', 'currentStock', 'isProduct']],
            'pagination' => ['total', 'current_page'],
        ]);
    });

    it('filters supplies by status', function (): void {
        Supply::factory()->for($this->company)->active()->count(2)->create();
        Supply::factory()->for($this->company)->lowStock()->count(1)->create();

        $response = $this->getJson('/api/company/supplies?status=low_stock');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'response');
    });
});

describe('DELETE /api/company/supply/{id}', function (): void {
    it('deletes a supply successfully', function (): void {
        /** @var Supply $supply */
        $supply = Supply::factory()->for($this->company)->create();

        $response = $this->deleteJson("/api/company/supply/{$supply->id}");

        $response->assertStatus(200);
        expect(Supply::find($supply->id))->toBeNull();
    });

    it('returns 404 for non-existent supply', function (): void {
        $response = $this->deleteJson('/api/company/supply/non-existent-id');

        $response->assertStatus(404);
    });
});
