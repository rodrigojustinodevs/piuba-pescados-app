<?php

declare(strict_types=1);

namespace Tests\Unit\Company;

use App\Application\DTOs\CompanyDTO;
use App\Application\Services\CompanyService;
use App\Domain\Enums\Status;
use App\Presentation\Controllers\CompanyController;
use App\Presentation\Requests\Company\CompanyStoreRequest;
use App\Presentation\Requests\Company\CompanyUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Mockery;
use Ramsey\Uuid\Uuid;

test('returns a successful response for index', function (): void {
    $companyData = [
        [
            'id'        => Uuid::uuid4()->toString(),
            'name'      => 'Company 1',
            'cnpj'      => '12345678000195',
            'address'   => '123 Main St',
            'phone'     => '1234567890',
            'status'    => Status::ACTIVE,
            'createdAt' => '2025-03-10 10:00:00',
            'updatedAt' => '2025-03-10 11:00:00',
        ],
        [
            'id'        => Uuid::uuid4()->toString(),
            'name'      => 'Company 2',
            'cnpj'      => '98765432000185',
            'address'   => '456 Elm St',
            'phone'     => '0987654321',
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
            $data['address'],
            $data['phone'],
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

    $companyService = Mockery::mock(CompanyService::class);
    $companyService->shouldReceive('showAllCompanies')
        ->once()
        ->andReturn($collection);

    $controller = new CompanyController($companyService);

    $response = $controller->index();

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
        '123 Main St',
        '1234567890',
        Status::ACTIVE,
        '2025-03-10 10:00:00',
        '2025-03-10 11:00:00'
    );

    $companyService = Mockery::mock(CompanyService::class);
    $companyService->shouldReceive('showCompany')
        ->with($companyId)
        ->once()
        ->andReturn($companyDTO);

    $controller = new CompanyController($companyService);

    $response = $controller->show($companyId);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $content = json_decode($response->getContent());
    expect($content->response->id)->toBe($companyId);
});

test('returns 404 when show company is empty', function (): void {
    $companyId    = Uuid::uuid4()->toString();
    $emptyCompany = new CompanyDTO('', '', '', '', '', Status::ACTIVE, null, null);

    $companyService = Mockery::mock(CompanyService::class);
    $companyService->shouldReceive('showCompany')
        ->with($companyId)
        ->once()
        ->andReturn($emptyCompany);

    $controller = new CompanyController($companyService);

    $response = $controller->show($companyId);

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
        '123 New St',
        '1234567890',
        Status::ACTIVE,
        '2025-03-10 10:00:00',
        '2025-03-10 11:00:00'
    );

    $companyService = Mockery::mock(CompanyService::class);
    $companyService->shouldReceive('create')
        ->once()
        ->with(['name' => 'New Company'])
        ->andReturn($companyDTO);

    $request = Mockery::mock(CompanyStoreRequest::class);
    $request->shouldReceive('validated')
        ->once()
        ->andReturn(['name' => 'New Company']);

    $controller = new CompanyController($companyService);

    $response = $controller->store($request);

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
        '456 Updated St',
        '0987654321',
        Status::ACTIVE,
        '2025-03-10 10:00:00',
        '2025-03-10 11:00:00'
    );

    $companyService = Mockery::mock(CompanyService::class);
    $companyService->shouldReceive('updateCompany')
        ->once()
        ->with($companyId, ['name' => 'Updated Company'])
        ->andReturn($companyDTO);

    $request = Mockery::mock(CompanyUpdateRequest::class);
    $request->shouldReceive('validated')
        ->once()
        ->andReturn(['name' => 'Updated Company']);

    $controller = new CompanyController($companyService);

    $response = $controller->update($request, $companyId);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $content = json_decode($response->getContent());
    expect($content->response->id)->toBe($companyId);
    expect($content->response->name)->toBe('Updated Company');
});

test('deletes a company via destroy', function (): void {
    $companyId = Uuid::uuid4()->toString();

    $companyService = Mockery::mock(CompanyService::class);
    $companyService->shouldReceive('deleteCompany')
        ->once()
        ->with($companyId)
        ->andReturn(true);

    $controller = new CompanyController($companyService);

    $response = $controller->destroy($companyId);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $content = json_decode($response->getContent());
    expect($content->message)->toBe('Company successfully deleted');
});

test('returns 404 when company deletion fails', function (): void {
    $companyId = Uuid::uuid4()->toString();

    $companyService = Mockery::mock(CompanyService::class);
    $companyService->shouldReceive('deleteCompany')
        ->once()
        ->with($companyId)
        ->andReturn(false);

    $controller = new CompanyController($companyService);

    $response = $controller->destroy($companyId);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_NOT_FOUND);

    $content = json_decode($response->getContent());
    expect($content->message)->toBe('Company not found');
});
