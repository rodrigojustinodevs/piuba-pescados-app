<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\SensorReading\CreateSensorReadingUseCase;
use App\Application\UseCases\SensorReading\DeleteSensorReadingUseCase;
use App\Application\UseCases\SensorReading\ListSensorReadingsUseCase;
use App\Application\UseCases\SensorReading\ShowSensorReadingUseCase;
use App\Application\UseCases\SensorReading\UpdateSensorReadingUseCase;
use App\Presentation\Requests\SensorReading\SensorReadingStoreRequest;
use App\Presentation\Requests\SensorReading\SensorReadingUpdateRequest;
use App\Presentation\Resources\SensorReading\SensorReadingResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Sensor Readings", description="Leituras de sensores")
 * @OA\Schema(
 *     schema="SensorReading",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="sensorId", type="string", format="uuid"),
 *     @OA\Property(property="companyId", type="string", format="uuid"),
 *     @OA\Property(property="value", type="number", format="float"),
 *     @OA\Property(property="unit", type="string", example="ppm"),
 *     @OA\Property(property="measuredAt", type="string", format="date-time"),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(
 *         property="sensor",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="sensorType", type="string"),
 *         @OA\Property(property="status", type="string"),
 *         @OA\Property(property="tankId", type="string", format="uuid")
 *     ),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
class SensorReadingController
{
    /**
     * @OA\Get(
     *     path="/company/sensor-readings",
     *     summary="Listar leituras de sensores",
     *     tags={"Sensor readings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="sensor_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="tank_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="date_from", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/SensorReading")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="first_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(
        Request $request,
        ListSensorReadingsUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only([
                'sensor_id',
                'tank_id',
                'date_from',
                'date_to',
                'per_page',
                'page',
            ]),
        );

        return ApiResponse::success(
            data:       SensorReadingResource::collection($paginator->items()),
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
     *     path="/company/sensor-reading/{id}",
     *     summary="Obter leitura por ID",
     *     tags={"Sensor readings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Leitura encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="response", ref="#/components/schemas/SensorReading")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(
        string $id,
        ShowSensorReadingUseCase $useCase,
    ): JsonResponse {
        $reading = $useCase->execute($id);

        return ApiResponse::success(
            data: new SensorReadingResource($reading->loadMissing(['sensor.tank'])),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/sensor-reading",
     *     summary="Registrar leitura de sensor",
     *     tags={"Sensor readings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sensor_id","value","unit","measured_at"},
     *             @OA\Property(property="sensor_id", type="string", format="uuid"),
     *             @OA\Property(property="value", type="number", format="float"),
     *             @OA\Property(property="unit", type="string", example="ppm"),
     *             @OA\Property(property="measured_at", type="string", format="date-time"),
     *             @OA\Property(property="notes", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Criado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="response", ref="#/components/schemas/SensorReading")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(
        SensorReadingStoreRequest $request,
        CreateSensorReadingUseCase $useCase,
    ): JsonResponse {
        $reading = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new SensorReadingResource($reading->loadMissing(['sensor.tank'])),
            message: 'Sensor reading created successfully.',
        );
    }

    /**
     * @OA\Put(
     *     path="/company/sensor-reading/{id}",
     *     summary="Atualizar leitura",
     *     tags={"Sensor readings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="value", type="number", format="float"),
     *             @OA\Property(property="unit", type="string"),
     *             @OA\Property(property="measured_at", type="string", format="date-time"),
     *             @OA\Property(property="notes", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Atualizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="response", ref="#/components/schemas/SensorReading")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(
        SensorReadingUpdateRequest $request,
        string $id,
        UpdateSensorReadingUseCase $useCase,
    ): JsonResponse {
        $reading = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    new SensorReadingResource($reading),
            message: 'Sensor reading updated successfully.',
        );
    }

    /**
     * @OA\Delete(
     *     path="/company/sensor-reading/{id}",
     *     summary="Excluir leitura",
     *     tags={"Sensor readings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Excluído",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(
        string $id,
        DeleteSensorReadingUseCase $useCase,
    ): JsonResponse {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Sensor reading deleted successfully.');
    }
}
