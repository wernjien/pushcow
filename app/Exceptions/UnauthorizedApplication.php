<?php

namespace App\Exceptions;

class UnauthorizedApplication extends ClientError
{
    /**
     * Create a new exception instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->data = [
            'token' => __('The specified device is not owned by the application.'),
        ];

        $this->status = 401;
    }
}
