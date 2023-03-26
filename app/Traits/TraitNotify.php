<?php

namespace App\Traits;

trait  TraitNotify
{
    function Notifications($FCM, $body, $photo_path, $post_id)
    {
        $SERVER_API_KEY = 'AAAAM8_eVkw:APA91bFKC5Ap-_RTQwx_0h2-JdVRMCKujDVx2oqE8LfDdi71aVrNPc4RNp-wjuedodwBW9SAoOsnyKFLBK9DXLbdThqcuETUlC_bI2rSQP0E7OrMFGe1y4coPdSDOgRen8X55RtjrKdQ';
        //$FCM = 'c5Ji0l4wQxeC_WCf5qWZC5:APA91bGkmd4VP5FdAW0EnCi09msyaqWHrB4WXXQFuomuY7DjAjGifScpkXFiTr2SAz0hZXAEx_AIVF5HaWgPzDt7PLqDNScWFXenLyEmQLqIm96D61H8a-XBUjPB4IY5YmDl5gLqrMTH';
        if ($photo_path == null)
            $photo_path = "defult";
        if ($post_id == null)
            $post_id = 0;

        $data = [

            "registration_ids" => [
                $FCM
            ],

            "notification" => [

                "title" => auth()->user()->name,

                "body" =>     $body, //Message body

                "sound" => "default",
                "color" => "#5864dd", //  or 1#a237e

                "icon" => "new",
                // "type" => "' . $type . '",

                "image" => $photo_path,

            ],
            "data" =>
            [
                "post_id" => $post_id,
                "user_id" => auth()->user()->id,
                "user_name" => auth()->user()->name,
                "user_photo" => auth()->user()->photo,
            ]

        ];

        $dataString = json_encode($data);

        $headers = [

            'Authorization: key=' . $SERVER_API_KEY,

            'Content-Type: application/json',

        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);
    }
}