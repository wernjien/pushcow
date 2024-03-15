<?php

namespace App\Exceptions;

use App\Support\Response;
use Exception;

abstract class ClientError extends Exception
{
    /**
     * The data to send to the client.
     *
     * @var mixed
     */
    protected $data;

    /**
     * The status code for the response.
     *
     * @var int
     */
    protected $status = 400;

    /**
     * The HTTP headers for the response.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Render an exception into an HTTP response.
     *
     * @return \App\Support\Response
     */
    public function render()
    {
        return Response::fail($this->data, $this->status, $this->headers);
    }
}
