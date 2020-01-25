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
        $data = [];
        if ($message->title != null || $message->body != null || $message->click_action != null) {
            $data = [
                'notification' => [
                    'title' => $message->title,
                    'body' => $message->body,
                    'sound' => $message->sound,
                    'color' => $message->color,
                    'click_action' => $message->click_action,
                ],
            ];

            // Set custom badge number when isset in PushMessage
            if (! empty($message->badge)) {
                $data['notification']['badge'] = $message->badge;
            }

            // Set icon when isset in PushMessage
            if (! empty($message->icon)) {
                $data['notification']['icon'] = $message->icon;
            }
        }

        if (! empty($message->extra)) {
            $data['data'] = $message->extra;
        }

        return $data;
    }
}
