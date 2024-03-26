<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\CreateMessageRequest;
use App\Jobs\ProcessMessageRequests;
use App\Repositories\MessageRequestRepository;
use App\Support\Response;

class MessageController extends Controller
{
    /**
     * Receive a create message request.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CreateMessageRequest $request)
    {
        $recipients = $request->input('recipients');
        $notification = $request->input('notification');
        $data = $request->input('data');
        $options = $request->input('options');
        $data = compact('recipients', 'notification', 'data', 'options');

        ProcessMessageRequests::dispatch(
            MessageRequestRepository::create($data)
        );

        return Response::success();
    }
}
