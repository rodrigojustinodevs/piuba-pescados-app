<?php

declare(strict_types=1);

use App\Domain\Enums\SupplyCategoryEnum;
use App\Domain\Enums\SupplyStatusEnum;
use App\Domain\Models\Supply;

describe('Supply::resolveStatus()', function (): void {
    it('defines status as low_stock when current_stock equals min_stock', function (): void {
        $supply                = new Supply();
        $supply->current_stock = 100;
        $supply->min_stock     = 100;
        $supply->status        = SupplyStatusEnum::ACTIVE;

        $supply->resolveStatus();

        expect($supply->status)->toBe(SupplyStatusEnum::LOW_STOCK);
    });

    it('defines status as low_stock when current_stock is below min_stock', function (): void {
        $supply                = new Supply();
        $supply->current_stock = 50;
        $supply->min_stock     = 100;
        $supply->status        = SupplyStatusEnum::ACTIVE;

        $supply->resolveStatus();

        expect($supply->status)->toBe(SupplyStatusEnum::LOW_STOCK);
    });

    it('defines status as active when current_stock is above min_stock', function (): void {
        $supply                = new Supply();
        $supply->current_stock = 200;
        $supply->min_stock     = 100;
        $supply->status        = SupplyStatusEnum::ACTIVE;

        $supply->resolveStatus();

        expect($supply->status)->toBe(SupplyStatusEnum::ACTIVE);
    });

    it('does not override inactive status even when stock is sufficient', function (): void {
        $supply                = new Supply();
        $supply->current_stock = 500;
        $supply->min_stock     = 100;
        $supply->status        = SupplyStatusEnum::INACTIVE;

        $supply->resolveStatus();

        expect($supply->status)->toBe(SupplyStatusEnum::INACTIVE);
    });

    it('does not override inactive status when stock is low', function (): void {
        $supply                = new Supply();
        $supply->current_stock = 10;
        $supply->min_stock     = 100;
        $supply->status        = SupplyStatusEnum::INACTIVE;

        $supply->resolveStatus();

        expect($supply->status)->toBe(SupplyStatusEnum::INACTIVE);
    });
});

describe('SupplyCategoryEnum', function (): void {
    it('has all required cases', function (): void {
        $values = array_map(fn (\App\Domain\Enums\SupplyCategoryEnum $case) => $case->value, SupplyCategoryEnum::cases());

        expect($values)->toContain('feed')
            ->toContain('medication')
            ->toContain('fertilizer')
            ->toContain('probiotic')
            ->toContain('equipment')
            ->toContain('packaging')
            ->toContain('finished_product')
            ->toContain('other');
    });

    it('returns portuguese labels', function (): void {
        expect(SupplyCategoryEnum::FEED->label())->toBe('Ração');
        expect(SupplyCategoryEnum::MEDICATION->label())->toBe('Medicamento');
        expect(SupplyCategoryEnum::FINISHED_PRODUCT->label())->toBe('Produto Acabado');
    });
});

describe('SupplyStatusEnum', function (): void {
    it('has all required cases', function (): void {
        $values = array_map(fn (\App\Domain\Enums\SupplyStatusEnum $case) => $case->value, SupplyStatusEnum::cases());

        expect($values)->toContain('active')
            ->toContain('inactive')
            ->toContain('low_stock');
    });

    it('returns portuguese labels', function (): void {
        expect(SupplyStatusEnum::ACTIVE->label())->toBe('Ativo');
        expect(SupplyStatusEnum::INACTIVE->label())->toBe('Inativo');
        expect(SupplyStatusEnum::LOW_STOCK->label())->toBe('Estoque Baixo');
    });
});

describe('SupplyInputDTO::fromArray()', function (): void {
    it('normalizes camelCase keys', function (): void {
        $dto = App\Application\DTOs\SupplyInputDTO::fromArray([
            'company_id'   => 'uuid-123',
            'name'         => 'Ração',
            'category'     => 'feed',
            'unit'         => 'kg',
            'unitCost'     => 3.5,
            'salePrice'    => 0.0,
            'currentStock' => 200.0,
            'minStock'     => 50.0,
            'isProduct'    => false,
            'status'       => 'active',
        ]);

        expect($dto->companyId)->toBe('uuid-123');
        expect($dto->category)->toBe(SupplyCategoryEnum::FEED);
        expect($dto->unitCost)->toBe(3.5);
        expect($dto->currentStock)->toBe(200.0);
    });

    it('sets default status to active', function (): void {
        $dto = App\Application\DTOs\SupplyInputDTO::fromArray([
            'company_id'    => 'uuid-123',
            'name'          => 'Test',
            'category'      => 'other',
            'unit'          => 'kg',
            'unit_cost'     => 0,
            'sale_price'    => 0,
            'current_stock' => 100,
            'min_stock'     => 50,
            'is_product'    => false,
        ]);

        expect($dto->status)->toBe(SupplyStatusEnum::ACTIVE);
    });
});
