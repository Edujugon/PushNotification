<?php
namespace Edujugon\PushNotification;


use GuzzleHttp\Client;

class Fcm extends Gcm
{

    public function __construct()
    {
        $this->url = 'https://fcm.googleapis.com/fcm/send';

        $this->config = $this->initializeConfig('fcm');
        
        $this->client = new Client();
    }
}