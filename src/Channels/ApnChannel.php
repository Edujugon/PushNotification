<?php

namespace Edujugon\PushNotification\Channels;

use Edujugon\PushNotification\Messages\PushMessage;
use Illuminate\Notifications\Notification;

class ApnChannel extends PushChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('Apn')) {
            return;
        }

        $message = $notification->toApn($notifiable);

        if (is_string($message)) {
            $message = new PushMessage($message);
        }

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
            $data['extraPayLoad'] = $message->extra;
        }

        return $this->push('apn', $to, $data, $message);
    }
}
