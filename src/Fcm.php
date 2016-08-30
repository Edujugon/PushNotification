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

    protected function addRequestFields($deviceTokens,$message)
    {
        $return = array();
        if(count($deviceTokens)) {
            $return['to'] = $deviceTokens[0];
        }

        if(isset($message['to'])) {
            $return['to'] = $message['to'];
            unset($message['to']);
        }

        if(isset($message['notification'])) {
            $return['notification'] = $message['notification'];
            unset($message['notification']);
        }

        if(isset($message['data'])) {
            $return['data'] = $message['data'];
        } else if(count($message)) {
            $return['data'] = $message;
        }

        return $return;
    }

    public function send(array $deviceTokens,array $message)
    {
        if(count($deviceTokens) > 1) {
            foreach($deviceTokens as $deviceToken) {
                parent::send([$deviceToken], $message);
            }
        } else {
            parent::send($deviceTokens, $message);
        }
    }

}