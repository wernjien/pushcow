<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    /**
     * Constant representing a pending send message.
     *
     * @var int
     */
    const STATUS_PENDING = 0;

    /**
     * Constant representing a successfully sent message.
     *
     * @var int
     */
    const STATUS_SUCCESS = 1;

    /**
     * Constant representing a failed to deliver message.
     *
     * @var int
     */
    const STATUS_FAILED = 2;
}
