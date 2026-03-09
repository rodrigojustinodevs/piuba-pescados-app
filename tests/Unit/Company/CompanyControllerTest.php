<?php

declare(strict_types=1);

namespace Tests\Unit\Company;

use App\Application\DTOs\CompanyDTO;
use App\Application\UseCases\Company\CreateCompanyUseCase;
use App\Application\UseCases\Company\DeleteCompanyUseCase;
use App\Application\UseCases\Company\ShowAllCompaniesUseCase;
use App\Application\UseCases\Company\ShowCompanyUseCase;
use App\Application\UseCases\Company\UpdateCompanyUseCase;
use App\Domain\Enums\Status;
use App\Presentation\Controllers\CompanyController;
use App\Presentation\Requests\Company\CompanyStoreRequest;
use App\Presentation\Requests\Company\CompanyUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Mockery;
use Ramsey\Uuid\Uuid;

test('returns a successful response for index', function (): void {
    $companyData = [
        [
            'id'      => Uuid::uuid4()->toString(),
            'name'    => 'Company 1',
            'cnpj'    => '12345678000195',
            'email'   => 'company1@test.com',
            'phone'   => '1234567890',
            'address' => [
                'street'       => '123 Main St',
                'number'       => '123',
                'complement'   => null,
                'neighborhood' => 'Downtown',
                'city'         => 'São Paulo',
                'state'        => 'SP',
                'zipCode'      => '01234-567',
            ],
            'status'    => Status::ACTIVE,
            'createdAt' => '2025-03-10 10:00:00',
            'updatedAt' => '2025-03-10 11:00:00',
        ],
        [
            'id'      => Uuid::uuid4()->toString(),
            'name'    => 'Company 2',
            'cnpj'    => '98765432000185',
            'email'   => 'company2@test.com',
            'phone'   => '0987654321',
            'address' => [
                'street'       => '456 Elm St',
                'number'       => '456',
                'complement'   => null,
                'neighborhood' => 'Uptown',
                'city'         => 'Rio de Janeiro',
                'state'        => 'RJ',
                'zipCode'      => '20000-000',
            ],
            'status'    => Status::INACTIVE,
            'createdAt' => '2025-03-10 10:00:00',
            'updatedAt' => '2025-03-10 11:00:00',
        ],
    ];

    $items = [];

    foreach ($companyData as $data) {
        $items[] = new CompanyDTO(
            $data['id'],
            $data['name'],
            $data['cnpj'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['status'],
            $data['createdAt'],
            $data['updatedAt']
        );
    }

    $collection             = new AnonymousResourceCollection(collect($items), null);
    $collection->additional = [
        'pagination' => [
            'total'        => count($items),
            'current_page' => 1,
            'last_page'    => 1,
            'first_page'   => 1,
            'per_page'     => count($items),
        ],
    ];

    $request = Mockery::mock(Request::class);
    $request->shouldReceive('integer')
        ->with('limit', 25)
        ->once()
        ->andReturn(25);
    $request->shouldReceive('filled')
        ->with('search')
        ->once()
        ->andReturn(false);
    $request->shouldReceive('input')
        ->never();

    $useCase = Mockery::mock(ShowAllCompaniesUseCase::class);
    $useCase->shouldReceive('execute')
        ->with(25, null)
        ->once()
        ->andReturn($collection);

    $controller = new CompanyController();
    $response   = $controller->index($request, $useCase);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $content = json_decode($response->getContent());

    expect(count($content->response))->toBe(count($items));
    expect($content->pagination->total)->toBe(count($items));
});

test('returns a company for show when found', function (): void {
    $companyId  = Uuid::uuid4()->toString();
    $companyDTO = new CompanyDTO(
        $companyId,
        'Company 1',
        '12345678000195',
        'company1@test.com',
        '1234567890',
        [
            'street'       => '123 Main St',
            'number'       => '123',
            'complement'   => null,
            'neighborhood' => 'Downtown',
            'city'         => 'São Paulo',
            'state'        => 'SP',
            'zipCode'      => '01234-567',
        ],
        Status::ACTIVE,
        '2025-03-10 10:00:00',
        '2025-03-10 11:00:00'
    );

    $useCase = Mockery::mock(ShowCompanyUseCase::class);
    $useCase->shouldReceive('execute')
        ->with($companyId)
        ->once()
        ->andReturn($companyDTO);

    $controller = new CompanyController();
    $response   = $controller->show($companyId, $useCase);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $content = json_decode($response->getContent());
    expect($content->response->id)->toBe($companyId);
});

test('returns 404 when show company is empty', function (): void {
    $companyId    = Uuid::uuid4()->toString();
    $emptyCompany = new CompanyDTO('', '', '', null, '', [], Status::ACTIVE);

    $useCase = Mockery::mock(ShowCompanyUseCase::class);
    $useCase->shouldReceive('execute')
        ->with($companyId)
        ->once()
        ->andReturn($emptyCompany);

    $controller = new CompanyController();
    $response   = $controller->show($companyId, $useCase);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_NOT_FOUND);

    $content = json_decode($response->getContent());
    expect($content->message)->toBe('Company not found');
});

test('creates a company via store', function (): void {
    $companyId  = Uuid::uuid4()->toString();
    $companyDTO = new CompanyDTO(
        $companyId,
        'New Company',
        '12345678000195',
        'newcompany@test.com',
        '1234567890',
        [
            'street'       => '123 New St',
            'number'       => '123',
            'complement'   => null,
            'neighborhood' => 'Downtown',
            'city'         => 'São Paulo',
            'state'        => 'SP',
            'zipCode'      => '01234-567',
        ],
        Status::ACTIVE,
        '2025-03-10 10:00:00',
        '2025-03-10 11:00:00'
    );

    $useCase = Mockery::mock(CreateCompanyUseCase::class);
    $useCase->shouldReceive('execute')
        ->once()
        ->with(['name' => 'New Company'])
        ->andReturn($companyDTO);

    $request = Mockery::mock(CompanyStoreRequest::class);
    $request->shouldReceive('validated')
        ->once()
        ->andReturn(['name' => 'New Company']);

    $controller = new CompanyController();
    $response   = $controller->store($request, $useCase);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_CREATED);

    $content = json_decode($response->getContent());
    expect($content->response->id)->toBe($companyId);
    expect($content->response->name)->toBe('New Company');
});

test('updates a company via update', function (): void {
    $companyId  = Uuid::uuid4()->toString();
    $companyDTO = new CompanyDTO(
        $companyId,
        'Updated Company',
        '98765432000185',
        'updated@test.com',
        '0987654321',
        [
            'street'       => '456 Updated St',
            'number'       => '456',
            'complement'   => null,
            'neighborhood' => 'Uptown',
            'city'         => 'Rio de Janeiro',
            'state'        => 'RJ',
            'zipCode'      => '20000-000',
        ],
        Status::ACTIVE,
        '2025-03-10 10:00:00',
        '2025-03-10 11:00:00'
    );

    $useCase = Mockery::mock(UpdateCompanyUseCase::class);
    $useCase->shouldReceive('execute')
        ->once()
        ->with($companyId, ['name' => 'Updated Company'])
        ->andReturn($companyDTO);

    $request = Mockery::mock(CompanyUpdateRequest::class);
    $request->shouldReceive('validated')
        ->once()
        ->andReturn(['name' => 'Updated Company']);

    $controller = new CompanyController();
    $response   = $controller->update($request, $companyId, $useCase);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $content = json_decode($response->getContent());
    expect($content->response->id)->toBe($companyId);
    expect($content->response->name)->toBe('Updated Company');
});

test('deletes a company via destroy', function (): void {
    $companyId = Uuid::uuid4()->toString();

    $useCase = Mockery::mock(DeleteCompanyUseCase::class);
    $useCase->shouldReceive('execute')
        ->once()
        ->with($companyId)
        ->andReturn(true);

    $controller = new CompanyController();
    $response   = $controller->destroy($companyId, $useCase);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $content = json_decode($response->getContent());
    expect($content->message)->toBe('Company successfully deleted');
});

test('returns 404 when company deletion fails', function (): void {
    $companyId = Uuid::uuid4()->toString();

    $useCase = Mockery::mock(DeleteCompanyUseCase::class);
    $useCase->shouldReceive('execute')
        ->once()
        ->with($companyId)
        ->andReturn(false);

    $controller = new CompanyController();
    $response   = $controller->destroy($companyId, $useCase);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_NOT_FOUND);

    $content = json_decode($response->getContent());
    expect($content->message)->toBe('Company not found');
});
