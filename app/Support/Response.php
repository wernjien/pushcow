<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class Response
{
    /**
     * Return a success response.
     */
    public static function success(
        mixed $data = null,
        int $status = 200,
        array $headers = []
    ): JsonResponse {
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], $status, $headers);
    }

    /**
     * Return a fail response.
     */
    public static function fail(
        mixed $data,
        int $status = 400,
        array $headers = []
    ): JsonResponse {
        return response()->json([
            'status' => 'fail',
            'data' => $data,
        ], $status, $headers);
    }

    /**
     * Return an error response.
     */
    public static function error(
        string $message,
        mixed $code = null,
        mixed $data = null,
        int $status = 400,
        array $headers = []
    ): JsonResponse {
        $data = array_filter([
            'status' => 'error',
            'message' => $message,
            'code' => $code,
            'data' => $data,
        ]);

        return response()->json($data, $status, $headers);
    }
}
