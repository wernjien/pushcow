<?php

namespace App;

use Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['application_id', 'device_id', 'token', 'user_id'];

    /**
     * The attributes that should be visible in serialization.
     *
     * @var array
     */
    protected $visible = ['device_id', 'token', 'user_id', 'updated_at'];

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
     * Scope a query to only include devices that match the combinations of the
     * given filters.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $data
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, array $data)
    {
        $deviceId = Arr::get($data, 'device_id');
        $token = Arr::get($data, 'token');
        $userId = Arr::get($data, 'user_id');

        return $query->when(! empty($deviceId), function ($query) use ($deviceId) {
                $query->where('device_id', $deviceId);
            })
            ->when(! empty($token), function ($query) use ($token) {
                $query->where('token', $token);
            })
            ->when(! empty($userId), function ($query) use ($userId) {
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

        if (is_string($keywords) && json_decode($keywords)) {
            $keywords = json_decode($keywords, true);
        }

        if ($exclude = is_array($keywords) && Arr::has($keywords, 'except')) {
            $keywords = Arr::get($keywords, 'except');
        }

        if (! is_array($keywords)) {
            $keywords = [$keywords];
        }

        if ($exclude) {
            return $query->whereNotIn('user_id', $keywords)
                ->orWhereNull('user_id');
        }

        return $query->whereIn('device_id', $keywords)
            ->orWhereIn('token', $keywords)
            ->orWhereIn('user_id', $keywords);
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

    /**
     * search device based on parameter supplied
     */
    public static function getUserDevice($appId,$userId=null)
    {
        if($userId)
        {
            $result = Device::where([
                ['application_id', '=', $appId],
                ['user_id', '=', $userId],
            ])->get();
        }
        else
        {   
            $result = Device::where([
                ['application_id', '=', $appId],
            ])->get();
        }

        return $result;
    }
}
