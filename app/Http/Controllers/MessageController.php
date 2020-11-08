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

        $devices = Device::search($recipients)->get();
        $compiledData = $this->compile($devices, $notification, $data);

        Message::insert($compiledData);

        return Response::success();
    }

    /**
     * Compile the data for mass insertion.
     *
     * @param  \Illuminate\Support\Collection  $devices
     * @param  string  $notification
     * @param  string  $data
     * @return array
     */
    protected function compile($devices, $notification, $data)
    {
        $rows = [];

        foreach ($devices as $device) {
            $rows[] = [
                'device_id' => $device->id,
                'notification' => $notification,
                'data' => $this->prependUserId($data, $device),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        return $rows;
    }

    /**
     * Prepend user ID to data.
     *
     * @param  string  $data
     * @param  \App\Device  $device
     * @return string
     */
    protected function prependUserId($data, $device)
    {
        $userId = data_get($device, 'user_id');

        if (empty($userId)) {
            return $data;
        }

        $data = array_merge(['_user_id' => $userId], json_decode($data, true));

        return json_encode($data);
    }
}
