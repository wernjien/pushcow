<?php

namespace App\Http\Controllers;

use App\Device;
use App\Message;
use App\Support\Response;
use App\Http\Requests\CreateMessage;
use Carbon\Carbon;

class MessageController extends Controller
{
    /**
     * Create a new message.
     *
     * @param  \App\Http\Requests\CreateMessage  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateMessage $request)
    {
        $recipients = $request->input('recipients');
        $notification = $request->input('notification');
        $data = $request->input('data');

        $deviceIds = Device::search($recipients)->pluck('id')->all();
        $compiledData = $this->compile($deviceIds, $notification, $data);

        Message::insert($compiledData);

        return Response::success();
    }

    /**
     * Compile the data for mass insertion.
     *
     * @param  array  $deviceIds
     * @param  string  $notification
     * @param  string  $data
     * @return array
     */
    protected function compile($deviceIds, $notification, $data)
    {
        $rows = [];

        for ($i = 0; $i < count($deviceIds); ++$i) { 
            $rows[] = [
                'device_id' => $deviceIds[$i],
                'notification' => $notification,
                'data' => $data,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        return $rows;
    }
}
