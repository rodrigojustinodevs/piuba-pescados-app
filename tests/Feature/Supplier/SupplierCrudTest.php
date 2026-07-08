<?php

declare(strict_types=1);

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Enums\SupplierCategoryEnum;
use App\Domain\Enums\SupplierStatusEnum;
use App\Domain\Models\Company;
use App\Domain\Models\Purchase;
use App\Domain\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $company = Company::factory()->create();

    $this->company = $company;

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

describe('POST /api/company/supplier', function (): void {
    it('creates a supplier normalizing the document to digits only', function (): void {
        $payload = [
            'companyId' => $this->company->id,
            'name'      => 'Fornecedor LTDA',
            'contact'   => 'João Silva',
            'phone'     => '(85) 99999-9999',
            'email'     => 'contato@empresa.com',
            'document'  => '12.345.678/0001-99',
            'category'  => SupplierCategoryEnum::FEED->value,
            'address'   => [
                'street'       => 'Rua A',
                'number'       => '100',
                'neighborhood' => 'Centro',
                'city'         => 'Fortaleza',
                'state'        => 'CE',
                'zipCode'      => '60000-000',
            ],
        ];

        $response = $this->postJson('/api/company/supplier', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('response.document', '12345678000199');
        $response->assertJsonPath('response.address.zipCode', '60000-000');
        $response->assertJsonPath('response.status', SupplierStatusEnum::ACTIVE->value);

        expect(Supplier::first()->document)->toBe('12345678000199');
    });

    it('rejects a document with an invalid length', function (): void {
        $payload = [
            'companyId' => $this->company->id,
            'name'      => 'Fornecedor LTDA',
            'contact'   => 'João Silva',
            'phone'     => '(85) 99999-9999',
            'email'     => 'contato@empresa.com',
            'document'  => '123',
        ];

        $response = $this->postJson('/api/company/supplier', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['document']);
    });

    it('rejects rating outside the 0-5 range', function (): void {
        $payload = [
            'companyId' => $this->company->id,
            'name'      => 'Fornecedor LTDA',
            'contact'   => 'João Silva',
            'phone'     => '(85) 99999-9999',
            'email'     => 'contato@empresa.com',
            'rating'    => 6,
        ];

        $response = $this->postJson('/api/company/supplier', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rating']);
    });
});

describe('GET /api/company/supplier/{id}', function (): void {
    it('computes totalPurchases and lastPurchaseAt from related purchases', function (): void {
        /** @var Supplier $supplier */
        $supplier = Supplier::factory()->create(['company_id' => $this->company->id]);

        Purchase::factory()->count(2)->create([
            'company_id'  => $this->company->id,
            'supplier_id' => $supplier->id,
            'order_date'  => now()->subDays(5),
        ]);

        Purchase::factory()->create([
            'company_id'  => $this->company->id,
            'supplier_id' => $supplier->id,
            'order_date'  => now(),
        ]);

        $response = $this->getJson("/api/company/supplier/{$supplier->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('response.totalPurchases', 3);
        expect($response->json('response.lastPurchaseAt'))->not->toBeNull();
    });
});

describe('PUT /api/company/supplier/{id}', function (): void {
    it('updates only the provided fields, keeping the rest unchanged', function (): void {
        /** @var Supplier $supplier */
        $supplier = Supplier::factory()->create([
            'company_id' => $this->company->id,
            'category'   => SupplierCategoryEnum::FEED->value,
        ]);

        $response = $this->putJson("/api/company/supplier/{$supplier->id}", [
            'rating' => 4.5,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('response.rating', 4.5);
        $response->assertJsonPath('response.category', SupplierCategoryEnum::FEED->value);
        $response->assertJsonPath('response.name', $supplier->name);
    });
});

describe('DELETE /api/company/supplier/{id}', function (): void {
    it('soft deletes a supplier', function (): void {
        /** @var Supplier $supplier */
        $supplier = Supplier::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/company/supplier/{$supplier->id}");

        $response->assertStatus(200);
        expect(Supplier::find($supplier->id))->toBeNull();
    });
});
