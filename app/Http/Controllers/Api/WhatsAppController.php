<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use GuzzleHttp\Client;

class WhatsAppController extends Controller
{
    public function sendMessage($number, $message)
    {
        $url = "https://api.fonnte.com/send";
        $token = env("FONNTE_TOKEN");

        try {
            $client = new Client();
            $client->post($url, [
                'headers' => [
                    'Authorization' => $token,
                ],
                'form_params' => [
                    'target' => $number,
                    'message' => $message,
                ],
            ]);
        } catch (Exception $e) {
           return $e->getMessage();
        }
    }
}
