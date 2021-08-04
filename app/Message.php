<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'notification' => 'array',
        'data' => 'array',
    ];

    /**
     * Get the device that the message sends to.
     *
     * @return \App\Device
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('device', function (Builder $builder) {
            $builder->has('device');
        });
    }
}
