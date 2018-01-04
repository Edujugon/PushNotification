<?php

namespace Edujugon\PushNotification\Channels;

class ApnChannel extends PushChannel
{
    protected function pushServiceName()
    {
        return 'apn';
    }

    protected function extraDataName()
    {
        return 'extraPayLoad';
    }

    protected function buildData($message)
    {
        $data = [
            'aps' => [
                'alert' => [
                    'title' => $message->title,
                    'body' => $message->body,
                ],
                'sound' => $message->sound,
            ],
        ];

        if (! empty($message->extra)) {
            $data[$this->extraDataName()] = $message->extra;
        }

        return $data;
    }
}
