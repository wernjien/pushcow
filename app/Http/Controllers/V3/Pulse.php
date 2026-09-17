<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Message;
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
        $lastReceivedAt = Message::lastReceivedAt();
        $lastPushedAt = Message::lastPushedAt();
        $format = 'Y-m-d H:i:s';

        return Response::success([
            'name' => data_get(auth()->user(), 'name'),
            'last_received_at' => optional($lastReceivedAt)->format($format),
            'last_pushed_at' => optional($lastPushedAt)->format($format),
        ]);
    }
}
