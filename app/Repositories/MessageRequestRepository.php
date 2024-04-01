<?php

namespace App\Repositories;

use App\MessageRequest;
use Illuminate\Support\Arr;

class MessageRequestRepository
{
    /**
     * Save a new message request and return the instance.
     *
     * @return \App\MessageRequest
     */
    public static function create(array $data)
    {
        if (! Arr::has($data, 'application_id') && auth()->check()) {
            $data['application_id'] = auth()->id();
        }

        return MessageRequest::create($data);
    }
}
