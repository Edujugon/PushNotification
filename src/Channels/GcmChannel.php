<?php

namespace Edujugon\PushNotification\Channels;

use Edujugon\PushNotification\Messages\PushMessage;

class GcmChannel extends PushChannel
{
    /**
     * {@inheritdoc}
     */
    protected function pushServiceName()
    {
        return 'gcm';
    }

    /**
     * {@inheritdoc}
     */
    protected function buildData(PushMessage $message)
    {
        $data = [
            'notification' => [
                'title' => $message->title,
                'body' => $message->body,
                'sound' => $message->sound,
            ],
        ];

        if (! empty($message->extra)) {
            $data['data'] = $message->extra;
        }

        return $data;
    }
}
