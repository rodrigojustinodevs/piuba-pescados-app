<?php

declare(strict_types=1);

namespace App\Presentation\Response;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiResponse
{
    /**
     * @param array<string, mixed>|null $data
     */
    public static function created(?array $data = null, string $message = 'Successfully created'): JsonResponse
    {
        return response()->json([
            'status'   => true,
            'response' => $data,
            'message'  => $message,
        ], Response::HTTP_CREATED);
    }

    /**
     * @param array<string, mixed>|null $data
     * @param array<string, mixed>|null $paginationData
     */
    public static function success(
        ?array $data = null,
        int $status = 200,
        string $message = 'Success',
        ?array $paginationData = null
    ): JsonResponse {
        $response = [
            'status'   => true,
            'response' => $data,
            'message'  => $message,
        ];

        if ($paginationData !== null) {
            $response['pagination'] = $paginationData;
        }

        return response()->json($response, $status);
    }

    /**
     * @param array<string, mixed>|null $data
     */
    public static function error(
        ?array $data = null,
        string $message = 'Error',
        int $status = Response::HTTP_BAD_REQUEST,
        bool $paramError = false
    ): JsonResponse {
        $status = $status > 0 ? $status : Response::HTTP_BAD_REQUEST;

        return response()->json([
            'status'     => false,
            'response'   => $data,
            'message'    => $message,
            'paramError' => $paramError,
        ], $status);
    }
}
