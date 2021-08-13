<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model implements AuthenticatableContract
{
    use Authenticatable, SoftDeletes;

    /**
     * Get the devices for the application.
     *
     * @return \Illuminate\Support\Collection
     *         \App\Device
     */
    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    /**
     * Get the messages for the application.
     *
     * @return \Illuminate\Support\Collection
     *         \App\Message
     */
    public function messages()
    {
        return $this->hasManyThrough(Message::class, Device::class);
    }
}
