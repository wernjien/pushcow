<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model implements AuthenticatableContract
{
    use Authenticatable, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'token',
        'api_version',
        'service_account',
        'server_key',
        'sender_id',
        'hps_client_id',
        'hps_client_secret',
    ];

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
     * Determine whether the application support topic.
     */
    public function hasTopicSupport(): bool
    {
        return $this->api_version >= 3;
    }
}
