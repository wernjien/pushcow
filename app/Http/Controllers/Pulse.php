<?php

namespace App\Http\Controllers;

use App\Support\Response;

class Pulse extends Controller
{
    /**
     * Ready to work!
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        return Response::success([
            'name' => data_get(auth()->user(), 'name'),
        ]);
    }
}
