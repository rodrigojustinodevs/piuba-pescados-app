<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Sensor\CreateSensorUseCase;
use App\Application\UseCases\Sensor\DeleteSensorUseCase;
use App\Application\UseCases\Sensor\ListSensorsUseCase;
use App\Application\UseCases\Sensor\ShowSensorUseCase;
use App\Application\UseCases\Sensor\UpdateSensorUseCase;
use App\Presentation\Requests\Sensor\SensorStoreRequest;
use App\Presentation\Requests\Sensor\SensorUpdateRequest;
use App\Presentation\Resources\Sensor\SensorResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Sensors", description="Sensores")
 * @OA\Schema(
 *     schema="Sensor",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="sensorType", type="string", example="temperature"),
 *     @OA\Property(property="installationDate", type="string", format="date", nullable=true),
 *     @OA\Property(property="status", type="string", example="active"),
 *     @OA\Property(
 *         property="tank",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
class SensorController
{
    /**
     * @OA\Get(
     *     path="/company/sensors",
     *     summary="List sensors",
     *     tags={"Sensors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="tank_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="sensor_type", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of sensors",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Sensor")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer", example=1),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="first_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=25)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(
        Request $request,
        ListSensorsUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only(['tank_id', 'sensor_type', 'status', 'per_page', 'page']),
        );

        return ApiResponse::success(
            data:       SensorResource::collection($paginator->items()),
            pagination: [
                'total'        => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'first_page'   => $paginator->firstPage(),
                'per_page'     => $paginator->perPage(),
            ],
        );
    }

    /**
     * @OA\Get(
     *     path="/company/sensor/{id}",
     *     summary="Get sensor by ID",
     *     tags={"Sensors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Sensor found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Sensor")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Sensor not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id, ShowSensorUseCase $useCase): JsonResponse
    {
        $sensor = $useCase->execute($id);

        return ApiResponse::success(
            data: new SensorResource($sensor->loadMissing('tank')),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/sensor",
     *     summary="Create a sensor",
     *     tags={"Sensors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tankId","sensorType","installationDate","status"},
     *             @OA\Property(property="tankId", type="string", format="uuid"),
     *             @OA\Property(property="sensorType", type="string", example="temperature"),
     *             @OA\Property(property="installationDate", type="string", format="date"),
     *             @OA\Property(property="status", type="string", example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sensor created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(property="response", ref="#/components/schemas/Sensor")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(SensorStoreRequest $request, CreateSensorUseCase $useCase): JsonResponse
    {
        $sensor = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new SensorResource($sensor),
            message: 'Sensor created successfully.',
        );
    }

    /**
     * @OA\Put(
     *     path="/company/sensor/{id}",
     *     summary="Update a sensor",
     *     tags={"Sensors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="tankId", type="string", format="uuid"),
     *             @OA\Property(property="sensorType", type="string"),
     *             @OA\Property(property="installationDate", type="string", format="date"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sensor updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Sensor")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Sensor not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(SensorUpdateRequest $request, string $id, UpdateSensorUseCase $useCase): JsonResponse
    {
        $sensor = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    new SensorResource($sensor),
            message: 'Sensor updated successfully.',
        );
    }

    /**
     * @OA\Delete(
     *     path="/company/sensor/{id}",
     *     summary="Delete a sensor",
     *     tags={"Sensors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Sensor deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response", nullable=true),
     *             @OA\Property(property="message", type="string", example="Sensor deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Sensor not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id, DeleteSensorUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Sensor deleted successfully.');
    }
}
