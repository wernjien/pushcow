<?php

namespace App;

use App\Scopes\ApplicationScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['application_id', 'uuid', 'token', 'user_id'];

    /**
     * The attributes that should be visible in serialization.
     *
     * @var array
     */
    protected $visible = ['uuid', 'token', 'user_id', 'updated_at'];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
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

        $keywords = json_decode($keywords);

        if (! is_array($keywords)) {
            $keywords = [$keywords];
        }

        return $query->whereIn('uuid', $keywords)
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

        static::addGlobalScope(new ApplicationScope);
    }
}
