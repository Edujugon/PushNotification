<?php
namespace Edujugon\PushNotification;


class Fcm extends Gcm
{

    public function __construct()
    {
        parent::__construct();

        $this->url = 'https://fcm.googleapis.com/fcm/send';
    }
}