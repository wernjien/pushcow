<?php

namespace App\Services;

use App\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class MessageServices
{
    /**
     * Search the messages that belongs to the given application and user.
     */
    public static function search(int $applicationId, ?string $userId = null): Collection
    {
        $application = Application::findOrFail($applicationId);
        $messages = $application->messages()
            ->when($userId, function (Builder $query, string $userId) {
                $query->toUser($userId);
            })
            ->get();

        return $messages;
    }
}
