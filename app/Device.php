<?php

namespace App;

use App\Support\StringParser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Device extends Model
{
    use SoftDeletes;

    /**
     * Constant representing Android platform.
     *
     * @var string
     */
    const PLATFORM_ANDROID = 'ANDROID';

    /**
     * Constant representing iOS platform.
     *
     * @var string
     */
    const PLATFORM_IOS = 'IOS';

    /**
     * Constant representing Huawei platform.
     *
     * @var string
     */
    const PLATFORM_HUAWEI = 'HUAWEI';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['application_id', 'platform', 'device_id', 'token', 'user_id'];

    /**
     * The attributes that should be visible in serialization.
     *
     * @var array
     */
    protected $visible = ['platform', 'device_id', 'token', 'user_id', 'updated_at'];

    /**
     * Get the application that the device registered to.
     *
     * @return \App\Application
     */
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the messages for the device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Scope a query to only include devices that match the combinations of the
     * given filters.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, array $data)
    {
        $deviceId = Arr::get($data, 'device_id');
        $token = Arr::get($data, 'token');
        $userId = Arr::get($data, 'user_id');

        return $query->when(! empty($deviceId), function ($query) use ($deviceId) {
            $query->where('device_id', $deviceId);
        })->when(! empty($token), function ($query) use ($token) {
            $query->where('token', $token);
        })->when(! empty($userId), function ($query) use ($userId) {
            $query->where('user_id', $userId);
        });
    }

    /**
     * Scope a query to only include devices that found in the given keywords.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $keywords
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $keywords)
    {
        if ($keywords == '*') {
            return $query;
        }

        $keywords = StringParser::auto($keywords);

        if (! is_array($keywords)) {
            $keywords = [$keywords];
        }

        if (Arr::has($keywords, 'except')) {
            $except = Arr::get($keywords, 'except');

            return $query->whereNotIn('user_id', $except)
                ->orWhereNull('user_id');
        }

        return $query->whereIn('device_id', $keywords)
            ->orWhereIn('token', $keywords)
            ->orWhereIn('user_id', $keywords);
    }

    /**
     * Get all the supported platforms.
     *
     * @return array
     */
    public static function getPlatforms()
    {
        return [
            static::PLATFORM_ANDROID,
            static::PLATFORM_IOS,
            static::PLATFORM_HUAWEI,
        ];
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('application', function (Builder $builder) {
            if (! request()->is('/')) {
                $builder->where('application_id', auth()->id());
            }
        });
    }
}
