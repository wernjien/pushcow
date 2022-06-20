<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMessageRequest;
use App\Jobs\ProcessMessageRequests;
use App\Support\Response;
use App\Repositories\MessageRequestRepository;

class MessageController extends Controller
{
    /**
     * Receive a create message request.
     *
     * @param  \App\Http\Requests\CreateMessageRequest  $request
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
