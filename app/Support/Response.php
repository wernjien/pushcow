<?php

namespace App\Support;

class Response
{
    /**
     * Return a success response.
     *
     * @param  mixed  $data
     * @param  int  $status
     * @param  array  $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data = null, $status = 200, array $headers = [])
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], $status, $headers);
    }

    /**
     * Return a fail response.
     *
     * @param  mixed  $data
     * @param  int  $status
     * @param  array  $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public static function fail($data, $status = 400, array $headers = [])
    {
        return response()->json([
            'status' => 'fail',
            'data' => $data,
        ], $status, $headers);
    }

    /**
     * Return an error response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  mixed  $data
     * @param  int  $status
     * @param  array  $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error(
        $message,
        $code = null,
        $data = null,
        $status = 400,
        array $headers = []
    ) {
        $data = array_filter([
            'status' => 'error',
            'message' => $message,
            'code' => $code,
            'data' => $data,
        ]);

        return response()->json($data, $status, $headers);
    }
}
