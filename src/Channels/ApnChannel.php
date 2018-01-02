<?php

namespace Edujugon\PushNotification\Channels;

use Edujugon\PushNotification\Events\NotificationPushed;
use Edujugon\PushNotification\Messages\PushMessage;
use Edujugon\PushNotification\PushNotification;
use Illuminate\Notifications\Notification;

class ApnChannel
{
    /**
     * @var \Edujugon\PushNotification\PushNotification
     */
    protected $push;

    /**
     * Create a new Apn channel instance.
     *
     * @param  \Edujugon\PushNotification\Facades\PushNotification $push
     * @return void
     */
    public function __construct(PushNotification $push)
    {
        $this->push = $push;
    }

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

        $this->push->setMessage($data)
            ->setService('apn')
            ->setDevicesToken($to);

        if (! empty($message->config)) {
            $this->push->setConfig($message->config);
        }

        $feedback = $this->push->send()
            ->getFeedback();

        broadcast(new NotificationPushed($this->push));

        return $feedback;
    }
}
