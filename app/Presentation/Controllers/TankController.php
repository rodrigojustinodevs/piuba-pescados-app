<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Tank\CreateTankUseCase;
use App\Application\UseCases\Tank\DeleteTankUseCase;
use App\Application\UseCases\Tank\GetTankTypesUseCase;
use App\Application\UseCases\Tank\ShowAllTanksUseCase;
use App\Application\UseCases\Tank\ShowTanksWithoutBatchesUseCase;
use App\Application\UseCases\Tank\ShowTankUseCase;
use App\Application\UseCases\Tank\UpdateTankUseCase;
use App\Presentation\Requests\Tank\TankStoreRequest;
use App\Presentation\Requests\Tank\TankUpdateRequest;
use App\Presentation\Resources\Tank\TankResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @OA\Tag(name="Tanks", description="Tanques")
 */
final class TankController
{
    /**
     * @OA\Get(
     *     path="/company/tanks",
     *     summary="List all tanks",
     *     tags={"Tanks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="company_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active","inactive"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of tanks",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="capacityLiters", type="integer"),
     *                     @OA\Property(property="location", type="string"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="tankType", type="object"),
     *                     @OA\Property(property="company", type="object")
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request, ShowAllTanksUseCase $useCase): JsonResponse
    {
        $result     = $useCase->execute($request->all());
        $collection = TankResource::collection($result->items());

        return ApiResponse::success($collection, Response::HTTP_OK, 'Success', [
            'total'        => $result->total(),
            'current_page' => $result->currentPage(),
            'last_page'    => $result->lastPage(),
            'first_page'   => $result->firstPage(),
            'per_page'     => $result->perPage(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/company/tanks/without-batches",
     *     summary="List tanks that have no batches linked",
     *     tags={"Tanks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="company_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active","inactive"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of tanks without batches",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="capacityLiters", type="integer"),
     *                     @OA\Property(property="location", type="string"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="tankType", type="object"),
     *                     @OA\Property(property="company", type="object")
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function tanksWithoutBatches(Request $request, ShowTanksWithoutBatchesUseCase $useCase): JsonResponse
    {
        $result     = $useCase->execute($request->all());
        $collection = TankResource::collection($result->items());

        return ApiResponse::success($collection, Response::HTTP_OK, 'Success', [
            'total'        => $result->total(),
            'current_page' => $result->currentPage(),
            'last_page'    => $result->lastPage(),
            'first_page'   => $result->firstPage(),
            'per_page'     => $result->perPage(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/company/tank/{id}",
     *     summary="Get a specific tank",
     *     tags={"Tanks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Tank ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tank details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="string",
     *                     format="uuid",
     *                     example="550e8400-e29b-41d4-a716-446655440000"
     *                 ),
     *                 @OA\Property(property="name", type="string", example="Tanque 01"),
     *                 @OA\Property(property="capacityLiters", type="integer", example=10000),
     *                 @OA\Property(property="location", type="string", example="Setor A - Bloco 3"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(
     *                     property="tankType",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string")
     *                 ),
     *                 @OA\Property(
     *                     property="company",
     *                     type="object",
     *                     @OA\Property(property="name", type="string")
     *                 ),
     *                 @OA\Property(property="createdAt", type="string", format="date-time"),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Tank not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id, ShowTankUseCase $useCase): JsonResponse
    {
        $tank = $useCase->execute($id);

        return ApiResponse::success(new TankResource($tank), Response::HTTP_OK, 'Success');
    }

    /**
     * @OA\Post(
     *     path="/company/tank",
     *     summary="Create a new tank",
     *     tags={"Tanks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"companyId", "tankTypeId", "name", "capacityLiters", "location", "status"},
     *             @OA\Property(
     *                 property="companyId",
     *                 type="string",
     *                 format="uuid",
     *                 description="Company UUID",
     *                 example="550e8400-e29b-41d4-a716-446655440000"
     *             ),
     *             @OA\Property(
     *                 property="tankTypeId",
     *                 type="string",
     *                 format="uuid",
     *                 description="Tank Type UUID",
     *                 example="550e8400-e29b-41d4-a716-446655440001"
     *             ),
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 maxLength=255,
     *                 description="Tank name",
     *                 example="Tanque 01"
     *             ),
     *             @OA\Property(
     *                 property="capacityLiters",
     *                 type="integer",
     *                 minimum=1,
     *                 description="Tank capacity in liters",
     *                 example=10000
     *             ),
     *             @OA\Property(
     *                 property="location",
     *                 type="string",
     *                 description="Tank location",
     *                 example="Setor A - Bloco 3"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"active", "inactive"},
     *                 description="Tank status",
     *                 example="active"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tank created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tank created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="capacityLiters", type="integer"),
     *                 @OA\Property(property="location", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="tankType", type="object"),
     *                 @OA\Property(property="company", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(TankStoreRequest $request, CreateTankUseCase $useCase): JsonResponse
    {
        $tank = $useCase->execute($request->validated());

        return ApiResponse::created(new TankResource($tank));
    }

    /**
     * @OA\Put(
     *     path="/company/tank/{id}",
     *     summary="Update a tank",
     *     tags={"Tanks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Tank ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="companyId",
     *                 type="string",
     *                 format="uuid",
     *                 description="Company UUID",
     *                 example="550e8400-e29b-41d4-a716-446655440000"
     *             ),
     *             @OA\Property(
     *                 property="tankTypeId",
     *                 type="string",
     *                 format="uuid",
     *                 description="Tank Type UUID",
     *                 example="550e8400-e29b-41d4-a716-446655440001"
     *             ),
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 maxLength=255,
     *                 description="Tank name",
     *                 example="Tanque 01"
     *             ),
     *             @OA\Property(
     *                 property="capacityLiters",
     *                 type="integer",
     *                 minimum=1,
     *                 description="Tank capacity in liters",
     *                 example=10000
     *             ),
     *             @OA\Property(
     *                 property="location",
     *                 type="string",
     *                 description="Tank location",
     *                 example="Setor A - Bloco 3"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"active", "inactive"},
     *                 description="Tank status",
     *                 example="active"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tank updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="capacityLiters", type="integer"),
     *                 @OA\Property(property="location", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="tankType", type="object"),
     *                 @OA\Property(property="company", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Tank not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(TankUpdateRequest $request, string $id, UpdateTankUseCase $useCase): JsonResponse
    {
        $tank = $useCase->execute($id, $request->validated());

        return ApiResponse::success(new TankResource($tank), Response::HTTP_OK, 'Success');
    }

    /**
     * @OA\Delete(
     *     path="/company/tank/{id}",
     *     summary="Delete a tank",
     *     tags={"Tanks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Tank ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tank deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tank successfully deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Tank not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id, DeleteTankUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return ApiResponse::success(null, Response::HTTP_OK, 'Tank successfully deleted');
    }

    /**
     * @OA\Get(
     *     path="/company/tank-types",
     *     summary="Get all tank types",
     *     tags={"Tanks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of tank types",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getTankTypes(GetTankTypesUseCase $useCase): JsonResponse
    {
        return ApiResponse::success(
            ['tankTypes' => $useCase->execute()],
            Response::HTTP_OK,
            'Success',
        );
    }
}
