<?php

declare(strict_types=1);

use App\Domain\Models\Company;
use App\Infrastructure\Persistence\CompanyRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pode criar uma empresa', function (): void {
    $repository = new CompanyRepository();

    $data = [
        'name'    => 'Empresa Teste',
        'cnpj'    => '12.345.678/0001-90',
        'address' => 'Rua Teste, 123',
        'phone'   => '(11) 99999-9999',
    ];

    $company = $repository->create($data);

    expect($company)->toBeInstanceOf(Company::class);
    expect($company->name)->toBe($data['name']);
    expect($company->cnpj)->toBe($data['cnpj']);
    expect($company->address)->toBe($data['address']);
    expect($company->phone)->toBe($data['phone']);
    expect(Company::count())->toBe(1);
});
