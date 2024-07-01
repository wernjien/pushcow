<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageRequest extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'application_id',
        'recipients',
        'notification',
        'data',
        'options',
    ];

    /**
     * Get the application for the message request.
     *
     * @return \App\Application
     */
    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
