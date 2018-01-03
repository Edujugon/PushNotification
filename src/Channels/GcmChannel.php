<?php

namespace Edujugon\PushNotification\Channels;

class GcmChannel extends PushChannel
{
    protected function pushServiceName()
    {
        return 'gcm';
    }

    protected function extraDataName()
    {
        return 'data';
    }

    protected function buildData($message)
    {
        $data = [
            'notification' => [
                'title' => $message->title,
                'body' => $message->body,
                'sound' => $message->sound,
            ],
        ];

        if (! empty($message->extra)) {
            $data[$this->extraDataName()] = $message->extra;
        }

        return $data;
    }
}
