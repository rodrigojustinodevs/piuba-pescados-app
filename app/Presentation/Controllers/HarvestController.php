<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\HarvestDTO;
use App\Application\UseCases\Harvest\CreateHarvestUseCase;
use App\Application\UseCases\Harvest\DeleteHarvestUseCase;
use App\Application\UseCases\Harvest\ListHarvestsUseCase;
use App\Application\UseCases\Harvest\ShowHarvestUseCase;
use App\Application\UseCases\Harvest\UpdateHarvestUseCase;
use App\Presentation\Requests\Harvest\HarvestStoreRequest;
use App\Presentation\Requests\Harvest\HarvestUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

/**
 * @OA\Tag(name="Harvests", description="Colheitas / Despesca")
 *
 * @OA\Schema(
 *     schema="HarvestSizeClassification",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="class", type="string", example="G"),
 *     @OA\Property(property="quantity", type="integer", example=100),
 *     @OA\Property(property="averageWeight", type="number", format="float", example=1200, description="Peso médio em gramas"),
 *     @OA\Property(property="pricePerKg", type="number", format="float", example=12.00),
 *     @OA\Property(property="totalWeight", type="number", format="float", example=120.0, description="Biomassa em kg (calculado)"),
 *     @OA\Property(property="revenue", type="number", format="float", example=1440.00, description="Receita da classe (calculado)")
 * )
 *
 * @OA\Schema(
 *     schema="Harvest",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="batch", type="object", nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string", example="Lote Tilápia 001")
 *     ),
 *     @OA\Property(property="tank", type="object", nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string", example="Tanque Camarão Tigre")
 *     ),
 *     @OA\Property(property="harvestDate", type="string", format="date", example="2026-06-09"),
 *     @OA\Property(property="type", type="string", enum={"total","partial","selective","emergency"}, example="partial"),
 *     @OA\Property(property="status", type="string", enum={"completed","scheduled","in_progress","cancelled"}, example="completed"),
 *     @OA\Property(property="destination", type="string", nullable=true, enum={"wholesale","retail","processing","restaurant","live_market","internal"}, example="wholesale"),
 *     @OA\Property(property="initialPopulation", type="integer", example=14),
 *     @OA\Property(property="harvestedQuantity", type="integer", example=12),
 *     @OA\Property(property="averageWeight", type="number", format="float", example=1200, description="Peso médio em gramas"),
 *     @OA\Property(property="totalWeight", type="number", format="float", example=120.0, description="Biomassa total em kg"),
 *     @OA\Property(property="pricePerKg", type="number", format="float", example=12.00),
 *     @OA\Property(property="totalRevenue", type="number", format="float", example=1440.00),
 *     @OA\Property(property="operationalCost", type="number", format="float", example=598.00),
 *     @OA\Property(property="netProfit", type="number", format="float", example=842.00, description="Lucro líquido (calculado)"),
 *     @OA\Property(property="survivalRate", type="number", format="float", example=85.7, description="Taxa de sobrevivência % (calculado)"),
 *     @OA\Property(property="clientDestination", type="string", nullable=true, example="teste"),
 *     @OA\Property(property="responsible", type="string", nullable=true, example="teste"),
 *     @OA\Property(property="notes", type="string", nullable=true, example="teste"),
 *     @OA\Property(property="sizeClassifications", type="array", @OA\Items(ref="#/components/schemas/HarvestSizeClassification")),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
class HarvestController
{
    /**
     * @OA\Get(
     *     path="/company/harvests",
     *     summary="List harvests",
     *     tags={"Harvest"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of harvests",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Harvest")
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
    public function index(ListHarvestsUseCase $useCase): JsonResponse
    {
        try {
            $harvests   = $useCase->execute();
            $data       = $harvests->toArray(request());
            $pagination = $harvests->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * @OA\Get(
     *     path="/company/harvest/{id}",
     *     summary="Get harvest by ID",
     *     tags={"Harvest"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Harvest found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Harvest")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Harvest not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id, ShowHarvestUseCase $useCase): JsonResponse
    {
        try {
            $harvest = $useCase->execute($id);

            if (! $harvest instanceof HarvestDTO || $harvest->isEmpty()) {
                return ApiResponse::error(null, 'Harvest not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($harvest->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Harvest not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/company/harvest",
     *     summary="Create harvest",
     *     tags={"Harvest"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"batchId","tankId","harvestDate","type","status","destination","initialPopulation","harvestedQuantity","averageWeight","sizeClassifications"},
     *             @OA\Property(property="batchId", type="string", format="uuid"),
     *             @OA\Property(property="tankId", type="string", format="uuid"),
     *             @OA\Property(property="harvestDate", type="string", format="date", example="2026-06-09"),
     *             @OA\Property(property="type", type="string", enum={"total","partial","selective","emergency"}, example="partial"),
     *             @OA\Property(property="status", type="string", enum={"completed","scheduled","in_progress","cancelled"}, example="completed"),
     *             @OA\Property(property="destination", type="string", enum={"wholesale","retail","processing","restaurant","live_market","internal"}, example="wholesale"),
     *             @OA\Property(property="initialPopulation", type="integer", example=14),
     *             @OA\Property(property="harvestedQuantity", type="integer", example=12),
     *             @OA\Property(property="averageWeight", type="number", format="float", example=1200),
     *             @OA\Property(
     *                 property="sizeClassifications",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"class","quantity","averageWeight","pricePerKg"},
     *                     @OA\Property(property="class", type="string", example="G"),
     *                     @OA\Property(property="quantity", type="integer", example=100),
     *                     @OA\Property(property="averageWeight", type="number", format="float", example=1200),
     *                     @OA\Property(property="pricePerKg", type="number", format="float", example=12.00)
     *                 )
     *             ),
     *             @OA\Property(property="clientDestination", type="string", nullable=true, example="teste"),
     *             @OA\Property(property="responsible", type="string", nullable=true, example="teste"),
     *             @OA\Property(property="operationalCost", type="number", format="float", nullable=true, example=598.00),
     *             @OA\Property(property="notes", type="string", nullable=true, example="teste")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Harvest created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(property="response", ref="#/components/schemas/Harvest")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(HarvestStoreRequest $request, CreateHarvestUseCase $useCase): JsonResponse
    {
        try {
            $harvest = $useCase->execute($request->validated());

            return ApiResponse::created($harvest->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Put(
     *     path="/company/harvest/{id}",
     *     summary="Update harvest",
     *     tags={"Harvest"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="batchId", type="string", format="uuid"),
     *             @OA\Property(property="tankId", type="string", format="uuid"),
     *             @OA\Property(property="harvestDate", type="string", format="date", example="2026-06-09"),
     *             @OA\Property(property="type", type="string", enum={"total","partial","selective","emergency"}),
     *             @OA\Property(property="status", type="string", enum={"completed","scheduled","in_progress","cancelled"}),
     *             @OA\Property(property="destination", type="string", enum={"wholesale","retail","processing","restaurant","live_market","internal"}),
     *             @OA\Property(property="initialPopulation", type="integer", example=14),
     *             @OA\Property(property="harvestedQuantity", type="integer", example=12),
     *             @OA\Property(property="averageWeight", type="number", format="float", example=1200),
     *             @OA\Property(
     *                 property="sizeClassifications",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="class", type="string", example="G"),
     *                     @OA\Property(property="quantity", type="integer", example=100),
     *                     @OA\Property(property="averageWeight", type="number", format="float", example=1200),
     *                     @OA\Property(property="pricePerKg", type="number", format="float", example=12.00)
     *                 )
     *             ),
     *             @OA\Property(property="clientDestination", type="string", nullable=true),
     *             @OA\Property(property="responsible", type="string", nullable=true),
     *             @OA\Property(property="operationalCost", type="number", format="float", nullable=true),
     *             @OA\Property(property="notes", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Harvest updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Harvest")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Harvest not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(HarvestUpdateRequest $request, string $id, UpdateHarvestUseCase $useCase): JsonResponse
    {
        try {
            $harvest = $useCase->execute($id, $request->validated());

            return ApiResponse::success($harvest->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Delete(
     *     path="/company/harvest/{id}",
     *     summary="Delete harvest",
     *     tags={"Harvest"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Harvest deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Harvest successfully deleted"),
     *             @OA\Property(property="response", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Harvest not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id, DeleteHarvestUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Harvest not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Harvest successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting harvest', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
