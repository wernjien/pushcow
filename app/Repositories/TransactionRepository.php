<?php

namespace App\Repositories;

use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class TransactionRepository
{
    /**
     * Save a new transaction and return the instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Transaction
     */
    public static function create(Request $request)
    {
        $transaction = new Transaction;

        $transaction->request_id = Str::uuid();
        $transaction->url = $request->url();
        $transaction->headers = array_filter($request->server->getHeaders());
        $transaction->request = $request->all() ?: null;

        return tap($transaction)->save();
    }

    /**
     * Update the transaction in the database.
     *
     * @param  \App\Transaction  $transaction
     * @param  \Illuminate\Http\JsonResponse  $response
     * @return bool
     */
    public static function update(Transaction $transaction, JsonResponse $response)
    {
        $transaction->response = json_decode($response->content());
        $transaction->status = $response->status();

        return $transaction->save();
    }
}
