<?php

declare(strict_types=1);

namespace Tests\Unit\Tank;

use App\Application\DTOs\TankDTO;
use App\Application\Services\TankService;
use App\Domain\Enums\Cultivation;
use App\Domain\Enums\Status;
use App\Presentation\Controllers\TankController;
use App\Presentation\Requests\Tank\TankStoreRequest;
use App\Presentation\Requests\Tank\TankUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Mockery;
use Ramsey\Uuid\Uuid;

test('returns a successful response for index', function (): void {
    $tankData = [
        [
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Tank 1',
            'capacity' => 1000,
            'volume' => 1000,
            "location" => "Location A",
            'status' => Status::ACTIVE,
            'cultivation' => Cultivation::DAYCARE,
            'tank_type'=> [
				'id' => '2fad96ea-2da6-491b-82e6-8d832f3dac41',
				'name' => 'Tanques Flutuantes'
            ],
			'company' => [
				'name' > 'Empresa X'
            ],
			'createdAt' => '2025-03-12 01:12:48',
			'updatedAt' => '2025-03-12 01:27:41'
        ],
        [
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Tank 2',
            'capacity' => 2000,
            'volume' => 1000,
            "location" => "Location A",
            'status' => Status::ACTIVE,
            'cultivation' => Cultivation::NURSERY,
            'tank_type'=> [
				'id' => '2fad96ea-2da6-491b-82e6-8d832f3dac41',
				'name' => 'Tanques Flutuantes'
            ],
			'company' => [
				'name' > 'Empresa X'
            ],
			'createdAt' => '2025-03-12 01:12:48',
			'updatedAt' => '2025-03-12 01:27:41'
        ],
    ];

    $items = [];

    foreach ($tankData as $data) {
        $items[] = new TankDTO(
            $data['id'],
            $data['name'],
            $data['capacity'],
            $data['volume'],
            $data['location'],
            $data['status'],
            $data['cultivation'],
            $data['tank_type'],
            $data['company'],
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


    $tankService = Mockery::mock(TankService::class);
    $tankService->shouldReceive('showAllTanks')->once()->andReturn($collection);

    $controller = new TankController($tankService);
    $response = $controller->index();

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $content = json_decode($response->getContent());

    expect(count($content->response))->toBe(count($items));
    expect($content->pagination->total)->toBe(count($items));
});

test('returns a tank for show when found', function (): void {
    $tankId = Uuid::uuid4()->toString();
    $tankDTO = new TankDTO(
        $tankId,
        'Tank 1',
        1000,
        1500,
        'Location A',
        Status::ACTIVE,
        Cultivation::DAYCARE,

    );

    $tankService = Mockery::mock(TankService::class);
    $tankService->shouldReceive('showTank')->with($tankId)->once()->andReturn($tankDTO);

    $controller = new TankController($tankService);
    $response = $controller->show($tankId);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
});

test('returns 404 when show tank is empty', function (): void {
    $tankId = Uuid::uuid4()->toString();

    $tankService = Mockery::mock(TankService::class);
    $tankService->shouldReceive('showTank')->with($tankId)->once()->andReturn(null);

    $controller = new TankController($tankService);
    $response = $controller->show($tankId);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_NOT_FOUND);
});

test('creates a tank via store', function (): void {
    $tankId = Uuid::uuid4()->toString();
    $tankDTO = new TankDTO(
        $tankId,
        'Tank 1',
        1000,
        1500,
        'Location A',
        Status::ACTIVE,
        Cultivation::DAYCARE,

    );

    $tankService = Mockery::mock(TankService::class);
    $tankService->shouldReceive('create')->once()->andReturn($tankDTO);

    $request = Mockery::mock(TankStoreRequest::class);
    $request->shouldReceive('validated')->once()->andReturn(['name' => 'New Tank', 'capacity' => 1500]);

    $controller = new TankController($tankService);
    $response = $controller->store($request);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_CREATED);
});

test('updates a tank via update', function (): void {
    $tankId = Uuid::uuid4()->toString();
    $tankDTO = new TankDTO(
        $tankId,
        'Tank 1',
        1000,
        1500,
        'Location A',
        Status::ACTIVE,
        Cultivation::DAYCARE,

    );

    $tankService = Mockery::mock(TankService::class);
    $tankService->shouldReceive('updateTank')->once()->andReturn($tankDTO);

    $request = Mockery::mock(TankUpdateRequest::class);
    $request->shouldReceive('validated')->once()->andReturn(['name' => 'Updated Tank', 'capacity' => 2000]);

    $controller = new TankController($tankService);
    $response = $controller->update($request, $tankId);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
});

test('deletes a tank via destroy', function (): void {
    $tankId = Uuid::uuid4()->toString();

    $tankService = Mockery::mock(TankService::class);
    $tankService->shouldReceive('deleteTank')->once()->andReturn(true);

    $controller = new TankController($tankService);
    $response = $controller->destroy($tankId);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
});

test('returns 404 when tank deletion fails', function (): void {
    $tankId = Uuid::uuid4()->toString();

    $tankService = Mockery::mock(TankService::class);
    $tankService->shouldReceive('deleteTank')->once()->andReturn(false);

    $controller = new TankController($tankService);
    $response = $controller->destroy($tankId);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_NOT_FOUND);
});
