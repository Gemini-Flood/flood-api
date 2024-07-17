<?php

namespace App\Services;

use App\Http\Controllers\Helpers\HelperController;
use Illuminate\Support\Facades\Http;

class FirebaseService extends HelperController
{

    public function sendPushNotification($title, $message, $userToken){

        $credentialsFilePath = "config\fcm.json";
        $client = new \Google_Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $apiurl = 'https://fcm.googleapis.com/v1/projects/floodai-1f951/messages:send';
        $client->fetchAccessTokenWithRefreshToken();
        $token = $client->getAccessToken();
        $access_token = $token['access_token'];

        $headers = [
             "Authorization: Bearer $access_token",
             'Content-Type: application/json'
        ];
        $test_data = [
            "title" => $title,
            "description" => $message,
        ];

        $data['data'] =  $test_data;

        $data['token'] = $userToken; // Retrive fcm_token from users table

        $payload['message'] = $data;
        $payload = json_encode($payload);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiurl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_exec($ch);
        $res = curl_close($ch);
        /* if($res){
            return true;
        } */
    }

}
