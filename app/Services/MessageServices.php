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
     */

    public static function search($applicationId, $userId = null)
    {
        $getMsg = Application::find($applicationId)->devices();
        $getMsg->with('messages');
        $getMsg->when($userId,function ($q,$userId){ 
            return $q->where('user_id',$userId);
        });
        $getMsg = $getMsg->get()->pluck('messages')->flatten();
        $collect = collect($getMsg);
        return $collect;
    }

} 