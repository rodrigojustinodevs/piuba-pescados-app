<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Biometry\CreateBiometryUseCase;
use App\Application\UseCases\Biometry\DeleteBiometryUseCase;
use App\Application\UseCases\Biometry\ListBiometriesUseCase;
use App\Application\UseCases\Biometry\ShowBiometryUseCase;
use App\Application\UseCases\Biometry\UpdateBiometryUseCase;
use App\Presentation\Requests\Biometry\BiometryStoreRequest;
use App\Presentation\Requests\Biometry\BiometryUpdateRequest;
use App\Presentation\Resources\Biometry\BiometryResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class BiometryController
{
    /**
     * @OA\Get(
     *     path="/company/biometries",
     *     summary="Listar biometrias",
     *     tags={"Biometry"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Itens por página",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de biometrias",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="batchId", type="string", format="uuid", description="ID do lote"),
     *                     @OA\Property(
     *                         property="batch",
     *                         type="object",
     *                         description="Lote (quando carregado)",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="name", type="string", nullable=true)
     *                     ),
     *                     @OA\Property(
     *                         property="biometryDate",
     *                         type="string",
     *                         format="date",
     *                         description="Data da biometria"
     *                     ),
     *                     @OA\Property(
     *                         property="averageWeight",
     *                         type="number",
     *                         format="float",
     *                         example=15.5,
     *                         description="Peso médio (g)"
     *                     ),
     *                     @OA\Property(
     *                         property="fcr",
     *                         type="number",
     *                         format="float",
     *                         example=1.2,
     *                         description="Fator de conversão alimentar"
     *                     ),
     *                     @OA\Property(property="sampleWeight", type="number", format="float", nullable=true,
     *                         description="Peso total da amostra (kg)"),
     *                     @OA\Property(property="sampleQuantity", type="integer", nullable=true,
     *                         description="Quantidade de peixes na amostra"),
     *                     @OA\Property(property="biomassEstimated", type="number", format="float", nullable=true,
     *                         description="Biomassa total estimada do tanque (kg)"),
     *                     @OA\Property(property="densityAtTime", type="number", format="float", nullable=true,
     *                         description="Densidade no momento da biometria (kg/m³)"),
     *                     @OA\Property(property="recommendedRation", type="number", format="float", nullable=true,
     *                         description="Recomendação de trato diário (kg)"),
     *                     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Display a listing of biometries.
     */
    public function index(ListBiometriesUseCase $useCase): JsonResponse
    {
        try {
            $biometries = $useCase->execute();
            $data       = $biometries->toArray(request());
            $pagination = $biometries->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * @OA\Get(
     *     path="/company/biometry/{id}",
     *     summary="Detalhar uma biometria",
     *     tags={"Biometry"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da biometria",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Biometria encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="batchId", type="string", format="uuid", description="ID do lote"),
     *                 @OA\Property(
     *                     property="batch",
     *                     type="object",
     *                     description="Lote (quando carregado)",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string", nullable=true)
     *                 ),
     *                 @OA\Property(
     *                     property="biometryDate",
     *                     type="string",
     *                     format="date",
     *                     description="Data da biometria"
     *                 ),
     *                 @OA\Property(
     *                     property="averageWeight",
     *                     type="number",
     *                     format="float",
     *                     example=15.5,
     *                     description="Peso médio (g)"
     *                 ),
     *                 @OA\Property(
     *                     property="fcr",
     *                     type="number",
     *                     format="float",
     *                     example=1.2,
     *                     description="Fator de conversão alimentar"
     *                 ),
     *                 @OA\Property(property="sampleWeight", type="number", format="float", nullable=true,
     *                     description="Peso total da amostra (kg)"),
     *                 @OA\Property(property="sampleQuantity", type="integer", nullable=true,
     *                     description="Quantidade de peixes na amostra"),
     *                 @OA\Property(property="biomassEstimated", type="number", format="float", nullable=true,
     *                     description="Biomassa total estimada do tanque (kg)"),
     *                 @OA\Property(property="densityAtTime", type="number", format="float", nullable=true,
     *                     description="Densidade no momento da biometria (kg/m³)"),
     *                 @OA\Property(property="recommendedRation", type="number", format="float", nullable=true,
     *                     description="Recomendação de trato diário (kg)"),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Biometry not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Display the specified biometry.
     */
    public function show(string $id, ShowBiometryUseCase $useCase): JsonResponse
    {
        try {
            $result = $useCase->execute($id);

            return ApiResponse::success(new BiometryResource($result), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Biometry not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/company/biometry",
     *     summary="Criar biometria",
     *     tags={"Biometry"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"batchId","biometryDate"},
     *             @OA\Property(property="batchId", type="string", format="uuid",
     *                 description="ID do lote (deve estar ativo)"),
     *             @OA\Property(
     *                 property="biometryDate",
     *                 type="string",
     *                 format="date",
     *                 example="2025-02-20",
     *                 description="Data da biometria"
     *             ),
     *             @OA\Property(
     *                 property="averageWeight",
     *                 type="number",
     *                 format="float",
     *                 example=15.5,
     *                 description="Peso médio em gramas. Obrigatório se não enviar sampleWeight/sampleQuantity"
     *             ),
     *             @OA\Property(
     *                 property="sampleWeight",
     *                 type="number",
     *                 format="float",
     *                 example=25.5,
     *                 description="Peso total da amostra (kg). Obrig. com sampleQuantity se não enviar averageWeight"
     *             ),
     *             @OA\Property(
     *                 property="sampleQuantity",
     *                 type="integer",
     *                 example=100,
     *                 description="Quantidade na amostra. Obrig. com sampleWeight se não enviar averageWeight. Mín. 1"
     *             ),
     *             @OA\Property(
     *                 property="fcr",
     *                 type="number",
     *                 format="float",
     *                 nullable=true,
     *                 description="Calculado automaticamente pelo backend quando omitido"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Biometria criada. FCR, biomassa, densidade e recomendação de trato calculados auto.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="batchId", type="string", format="uuid"),
     *                 @OA\Property(property="biometryDate", type="string", format="date"),
     *                 @OA\Property(property="averageWeight", type="number", format="float", example=15.5),
     *                 @OA\Property(property="fcr", type="number", format="float", example=1.2),
     *                 @OA\Property(property="sampleWeight", type="number", format="float", nullable=true),
     *                 @OA\Property(property="sampleQuantity", type="integer", nullable=true),
     *                 @OA\Property(property="biomassEstimated", type="number", format="float", nullable=true),
     *                 @OA\Property(property="densityAtTime", type="number", format="float", nullable=true),
     *                 @OA\Property(property="recommendedRation", type="number", format="float", nullable=true),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Store a newly created biometry.
     */
    public function store(BiometryStoreRequest $request, CreateBiometryUseCase $useCase): JsonResponse
    {
        try {
            $biometry = $useCase->execute($request->validated());

            return ApiResponse::created(new BiometryResource($biometry));
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Put(
     *     path="/company/biometry/{id}",
     *     summary="Atualizar biometria",
     *     tags={"Biometry"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da biometria",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="batchId", type="string", format="uuid", description="ID do lote (opcional)"),
     *             @OA\Property(
     *                 property="biometryDate",
     *                 type="string",
     *                 format="date",
     *                 example="2025-02-20",
     *                 description="Data da biometria (opcional)"
     *             ),
     *             @OA\Property(
     *                 property="averageWeight",
     *                 type="number",
     *                 format="float",
     *                 example=15.5,
     *                 description="Peso médio (g). Se omitido com sampleWeight/sampleQuantity, é recalculado."
     *             ),
     *             @OA\Property(
     *                 property="sampleWeight",
     *                 type="number",
     *                 format="float",
     *                 description="Peso total da amostra (kg) (opcional)"
     *             ),
     *             @OA\Property(
     *                 property="sampleQuantity",
     *                 type="integer",
     *                 description="Quantidade de peixes na amostra (opcional)"
     *             ),
     *             @OA\Property(
     *                 property="fcr",
     *                 type="number",
     *                 format="float",
     *                 nullable=true,
     *                 description="Recalculado automaticamente pelo backend."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Biometria atualizada. FCR, biomassa, densidade e recomendação de trato são recalculados.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="batchId", type="string", format="uuid"),
     *                 @OA\Property(property="biometryDate", type="string", format="date"),
     *                 @OA\Property(property="averageWeight", type="number", format="float", example=15.5),
     *                 @OA\Property(property="fcr", type="number", format="float", example=1.2),
     *                 @OA\Property(property="sampleWeight", type="number", format="float", nullable=true),
     *                 @OA\Property(property="sampleQuantity", type="integer", nullable=true),
     *                 @OA\Property(property="biomassEstimated", type="number", format="float", nullable=true),
     *                 @OA\Property(property="densityAtTime", type="number", format="float", nullable=true),
     *                 @OA\Property(property="recommendedRation", type="number", format="float", nullable=true),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Biometry not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Update the specified biometry.
     */
    public function update(BiometryUpdateRequest $request, string $id, UpdateBiometryUseCase $useCase): JsonResponse
    {
        try {
            $biometry = $useCase->execute($id, $request->validated());

            return ApiResponse::success(new BiometryResource($biometry), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Delete(
     *     path="/company/biometry/{id}",
     *     summary="Excluir biometria",
     *     tags={"Biometry"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da biometria",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Biometria excluída com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Biometry successfully deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Biometry not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Remove the specified biometry.
     */
    public function destroy(string $id, DeleteBiometryUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Biometry not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Biometry successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting biometry', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
