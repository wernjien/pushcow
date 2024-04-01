<?php

namespace App;

use App\Services\PushNotificationService;
use App\Support\StringParser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    protected $fillable = [
        'application_id',
        'platform',
        'device_id',
        'token',
        'user_id',
    ];

    /**
     * The attributes that should be visible in serialization.
     *
     * @var array
     */
    protected $visible = [
        'platform',
        'device_id',
        'token',
        'user_id',
        'updated_at',
    ];

    /**
     * Get the application that the device registered to.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the messages for the device.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the subscribed topics for the device.
     */
    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    /**
     * Scope a query to only include devices that match the combinations of the
     * given filters.
     */
    public function scopeFilter(Builder $query, array $data): Builder
    {
        $deviceId = Arr::get($data, 'device_id');
        $token = Arr::get($data, 'token');
        $userId = Arr::get($data, 'user_id');

        return $query->when(! empty($deviceId), function (Builder $query) use ($deviceId) {
            $query->where('device_id', $deviceId);
        })->when(! empty($token), function (Builder $query) use ($token) {
            $query->where('token', $token);
        })->when(! empty($userId), function (Builder $query) use ($userId) {
            $query->where('user_id', $userId);
        });
    }

    /**
     * Scope a query to only include devices that found in the given keywords.
     */
    public function scopeSearch(Builder $query, string $keywords): Builder
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
     * Subscribe to a topic.
     */
    public function subscribe(string $topic): void
    {
        $service = app(PushNotificationService::class, ['device' => $this]);
        $service->subscribeToTopic($topic);
    }

    /**
     * Get all the supported platforms.
     */
    public static function getPlatforms(): array
    {
        return [
            static::PLATFORM_ANDROID,
            static::PLATFORM_IOS,
            static::PLATFORM_HUAWEI,
        ];
    }

    /**
     * The "booting" method of the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('application', function (Builder $builder) {
            if (! request()->is('/')) {
                $builder->where('application_id', auth()->id());
            }
        });
    }
}
