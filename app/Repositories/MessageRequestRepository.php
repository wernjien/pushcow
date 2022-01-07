<?php

namespace App\Repositories;

use Arr;
use App\MessageRequest;

class MessageRequestRepository
{
    /**
     * Save a new message request and return the instance.
     *
     * @param  array  $data
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
