<?php

namespace App\Repositories;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransactionRepository
{
    /**
     * Save a new transaction and return the instance.
     */
    public static function create(Request $request): string
    {
        $data = [
            'url' => $request->url(),
            'headers' => array_filter($request->server->getHeaders()),
            'request' => $request->all() ?: null,
        ];

        return tap(Str::ulid(), function ($requestId) use ($data) {
            Log::channel('transactions')->info($requestId, $data);
        });
    }

    /**
     * Update the transaction in the database.
     */
    public static function update(string $requestId, JsonResponse $response): void
    {
        $data = [
            'response' => json_decode($response->content(), true),
            'status' => $response->status(),
        ];

        Log::channel('transactions')->info($requestId, $data);
    }
}
