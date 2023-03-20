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
        'options' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_id',
        'notification',
        'data',
        'options',
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
     * Get the timestamp of the last received message.
     *
     * @return \Carbon\Carbon
     */
    public static function lastReceivedAt()
    {
        $lastMessage = static::latest()->first();

        return data_get($lastMessage, 'created_at');
    }

    /**
     * Get the timestamp of the last pushed message.
     *
     * @return \Carbon\Carbon
     */
    public static function lastPushedAt()
    {
        $lastPushed = static::where('status', '!=', static::STATUS_PENDING)
            ->latest()
            ->first();

        return data_get($lastPushed, 'updated_at');
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
