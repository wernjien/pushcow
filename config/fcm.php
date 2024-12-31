<?php

/**
 * @deprecated This configuration file is used by the legacy Firebase Cloud Messaging service.
 * Please switch to Firebase Cloud Messaging V1 and this configuration file will be removed in next major release.
 * @see App\Services\Firebase\V1\CloudMessaging
 */

return [

    'driver' => env('FCM_PROTOCOL', 'http'),

    'log_enabled' => false,

    'http' => [
        'server_key' => env('FCM_SERVER_KEY'),
        'sender_id' => env('FCM_SENDER_ID'),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30,
    ],

];
