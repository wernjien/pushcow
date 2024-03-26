<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Topic extends Model
{
    use HasFactory;

    /**
     * Get the device that subscribed to the topic.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
