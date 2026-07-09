<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Enums\SupplyCategoryEnum;
use App\Domain\Enums\SupplyStatusEnum;
use App\Domain\Models\Company;
use App\Domain\Models\Supplier;
use App\Domain\Models\Supply;
use Illuminate\Database\Seeder;

class SupplySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        if (! $company) {
            $this->command->warn('Nenhuma empresa encontrada. Execute o seeder de empresas primeiro.');

            return;
        }

        $supplier = Supplier::where('company_id', $company->id)->first();

        $supplies = [
            [
                'sku'           => 'RAC-INIC-001',
                'name'          => 'Ração Inicial Tilápia',
                'category'      => SupplyCategoryEnum::FEED->value,
                'unit'          => 'kg',
                'unit_cost'     => 3.50,
                'sale_price'    => 0.00,
                'current_stock' => 2000.000,
                'min_stock'     => 500.000,
                'supplier_id'   => $supplier?->id,
                'is_product'    => false,
                'status'        => SupplyStatusEnum::ACTIVE->value,
                'description'   => 'Ração extrusada para fase inicial de engorda (alevinagem).',
            ],
            [
                'sku'           => 'RAC-CRESC-002',
                'name'          => 'Ração Crescimento Tilápia',
                'category'      => SupplyCategoryEnum::FEED->value,
                'unit'          => 'kg',
                'unit_cost'     => 3.20,
                'sale_price'    => 0.00,
                'current_stock' => 80.000,
                'min_stock'     => 500.000,
                'supplier_id'   => $supplier?->id,
                'is_product'    => false,
                'status'        => SupplyStatusEnum::LOW_STOCK->value,
                'description'   => 'Ração extrusada para fase de crescimento.',
            ],
            [
                'sku'           => 'MED-OXITE-001',
                'name'          => 'Oxitetraciclina 500mg',
                'category'      => SupplyCategoryEnum::MEDICATION->value,
                'unit'          => 'unit',
                'unit_cost'     => 0.80,
                'sale_price'    => 0.00,
                'current_stock' => 1000.000,
                'min_stock'     => 200.000,
                'supplier_id'   => $supplier?->id,
                'is_product'    => false,
                'status'        => SupplyStatusEnum::ACTIVE->value,
                'description'   => 'Antibiótico para tratamento de infecções bacterianas.',
            ],
            [
                'sku'           => 'PROB-001',
                'name'          => 'Probiótico Aquático',
                'category'      => SupplyCategoryEnum::PROBIOTIC->value,
                'unit'          => 'kg',
                'unit_cost'     => 45.00,
                'sale_price'    => 0.00,
                'current_stock' => 50.000,
                'min_stock'     => 10.000,
                'supplier_id'   => $supplier?->id,
                'is_product'    => false,
                'status'        => SupplyStatusEnum::ACTIVE->value,
                'description'   => 'Melhora a qualidade da água e saúde intestinal dos peixes.',
            ],
            [
                'sku'           => 'EMB-CAIXA-001',
                'name'          => 'Caixa de Papelão 10kg',
                'category'      => SupplyCategoryEnum::PACKAGING->value,
                'unit'          => 'unit',
                'unit_cost'     => 2.50,
                'sale_price'    => 0.00,
                'current_stock' => 500.000,
                'min_stock'     => 100.000,
                'supplier_id'   => $supplier?->id,
                'is_product'    => false,
                'status'        => SupplyStatusEnum::ACTIVE->value,
                'description'   => 'Embalagem para distribuição de peixes processados.',
            ],
            [
                'sku'           => 'TILAP-INT-001',
                'name'          => 'Tilápia Inteira Congelada',
                'category'      => SupplyCategoryEnum::FINISHED_PRODUCT->value,
                'unit'          => 'kg',
                'unit_cost'     => 8.00,
                'sale_price'    => 14.50,
                'current_stock' => 300.000,
                'min_stock'     => 50.000,
                'supplier_id'   => null,
                'is_product'    => true,
                'status'        => SupplyStatusEnum::ACTIVE->value,
                'description'   => 'Produto acabado para venda direta ao consumidor.',
            ],
            [
                'sku'           => 'EQP-AERADOR-001',
                'name'          => 'Aerador Elétrico 1CV',
                'category'      => SupplyCategoryEnum::EQUIPMENT->value,
                'unit'          => 'unit',
                'unit_cost'     => 850.00,
                'sale_price'    => 0.00,
                'current_stock' => 5.000,
                'min_stock'     => 2.000,
                'supplier_id'   => $supplier?->id,
                'is_product'    => false,
                'status'        => SupplyStatusEnum::ACTIVE->value,
                'description'   => 'Aerador para viveiros de até 2 hectares.',
            ],
        ];

        foreach ($supplies as $data) {
            Supply::firstOrCreate(
                ['sku' => $data['sku'], 'company_id' => $company->id],
                array_merge($data, ['company_id' => $company->id]),
            );
        }

        $this->command->info('SupplySeeder: ' . count($supplies) . ' insumos criados para a empresa ' . $company->name);
    }
}
