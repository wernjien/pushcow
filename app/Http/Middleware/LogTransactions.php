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
        $transaction = TransactionRepository::create($request);

        return tap($next($request), function ($response) use ($transaction) {
            $response->header('X-Correlation-ID', $transaction->request_id);

            TransactionRepository::update($transaction, $response);
        });
    }
}
