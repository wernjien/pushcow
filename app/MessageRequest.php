<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MessageRequest extends Model
{
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
