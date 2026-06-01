<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model implements AuthenticatableContract
{
    use Authenticatable, SoftDeletes;

    /**
     * Get the devices for the application.
     *
     * @return HasMany
     */
    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    /**
     * Get the messages for the application.
     *
     * @return HasMany
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Determine whether FCM Legacy APIs should be used.
     */
    public function shouldUseFCMLegacy(): bool
    {
        return $this->api_version < 3; // Disabled after v3.
    }

    /**
     * Determine whether the application support topic.
     */
    public function hasTopicSupport(): bool
    {
        return $this->api_version >= 3;
    }
}
