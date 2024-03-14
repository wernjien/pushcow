<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Repositories\TransactionRepository;

class LogTransactions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
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
