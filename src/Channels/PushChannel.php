<?php

namespace Edujugon\PushNotification\Channels;

use Edujugon\PushNotification\Events\NotificationPushed;
use Edujugon\PushNotification\Messages\PushMessage;
use Edujugon\PushNotification\PushNotification;

abstract class PushChannel
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
     * Send the push notification.
     *
     * @param  string $service
     * @param  mixed $to
     * @param  array $data
     * @param  \Edujugon\PushNotification\Messages\PushMessage $message
     * @return mixed
     */
    protected function push($service, $to, $data, PushMessage $message)
    {
        $this->push->setMessage($data)
            ->setService($service)
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
