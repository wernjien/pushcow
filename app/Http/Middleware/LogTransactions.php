<?php

namespace App\Http\Middleware;

use App\Repositories\TransactionRepository;
use Closure;
use Illuminate\Http\Request;

class LogTransactions
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $requestId = TransactionRepository::create($request);

        return tap($next($request), function ($response) use ($requestId) {
            $response->header('X-Correlation-ID', $requestId);

            TransactionRepository::update($requestId, $response);
        });
    }
}
