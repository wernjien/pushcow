<?php

namespace App\Services;

use App\Application;
use Illuminate\Support\Collection;

class MessageServices
{
    /**
     * Search the messages that belongs to the given application and user.
     *
     * @param  int  $applicationId
     * @param  string  $userId
     * @return \Illuminate\Support\Collection
     *         \App\Message
     */
    public static function search($applicationId, $userId = null)
    {
        $application = Application::findOrFail($applicationId);
        $messages = $application->messages()
            ->when($userId, function ($query, $userId) {
                $query->whereHas('device', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                });
            })
            ->get();

        return $messages;
    }
}
