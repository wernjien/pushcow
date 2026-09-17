<?php

namespace App\Repositories;

use App\MessageRequest;
use Illuminate\Support\Arr;

class MessageRequestRepository
{
    /**
     * Save a new message request and return the instance.
     *
     * @return MessageRequest
     */
    public static function create(array $data)
    {
        if (! Arr::has($data, 'application_id') && auth()->check()) {
            $data['application_id'] = auth()->id();
        }

        if (is_array(Arr::get($data, 'recipients'))) {
            $data['recipients'] = json_encode($data['recipients']);
        }

        return MessageRequest::create($data);
    }
}
