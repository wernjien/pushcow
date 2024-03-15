<?php

namespace App\Services;

use App\Application;

class MessageServices
{
    /**
     * Search the messages that belongs to the given application and user.
     *
     * @param  int  $applicationId
     * @param  string  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function search($applicationId, $userId = null)
    {
        $application = Application::findOrFail($applicationId);
        $messages = $application->messages()
            ->when($userId, function ($query, $userId) {
                $query->toUser($userId);
            })
            ->get();

        return $messages;
    }
}
