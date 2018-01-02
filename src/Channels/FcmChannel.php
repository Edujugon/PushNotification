<?php

namespace Edujugon\PushNotification\Channels;

use Edujugon\PushNotification\Events\NotificationPushed;
use Edujugon\PushNotification\Messages\PushMessage;
use Edujugon\PushNotification\PushNotification;
use Illuminate\Notifications\Notification;

class FcmChannel
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
        if (! $to = $notifiable->routeNotificationFor('Fcm')) {
            return;
        }

        $message = $notification->toFcm($notifiable);

        if (is_string($message)) {
            $message = new PushMessage($message);
        }

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

        $this->push->setMessage($data)
            ->setService('fcm')
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
