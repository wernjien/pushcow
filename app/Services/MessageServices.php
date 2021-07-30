<?php

namespace App\Services;

use App\Message;
use App\Device;
use Illuminate\Support\Collection;

class MessageServices 
{
    /**
     * Search the messages that belongs to the given application and user.
     *
     * @param  int  $applicationId
     * @param  string  $userId
     * @return \Illuminate\Support\Collection
     */

    public function search($applicationId, $userId = null)
    {
        $device = Device::getUserDevice($applicationId,$userId);

        foreach($device as $list)
        {
            $messages = Message::displayAllMessages($list['id']);
            $collect = collect($messages);
            return $collect->toArray();
        }
    }

} 