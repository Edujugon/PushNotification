<?php

namespace Edujugon\PushNotification\Channels;

use Edujugon\PushNotification\Events\NotificationPushed;
use Edujugon\PushNotification\Messages\PushMessage;
use Edujugon\PushNotification\PushNotification;
use Illuminate\Notifications\Notification;

abstract class PushChannel
{
    /**
     * @var \Edujugon\PushNotification\PushNotification
     */
    protected $push;

    /**
     * Create a new Apn channel instance.
     *
     * @param  \Edujugon\PushNotification\PushNotification $push
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
        if (! $to = $notifiable->routeNotificationFor($this->notificationFor())) {
            return;
        }

        $message = $this->buildMessage($notifiable,$notification);

        $data = $this->buildData($message);

        $this->push($this->pushServiceName(), $to, $data, $message);
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

            if (! empty($message->config['apiKey'])) {
                $this->push->setApiKey($message->config['apiKey']);
            }
        }

        $feedback = $this->push->send()
            ->getFeedback();

        if(function_exists('broadcast')) {
            broadcast(new NotificationPushed($this->push));
        }elseif (function_exists('event')) {
            event(new NotificationPushed($this->push));
        }

        return $feedback;
    }

    protected function buildMessage($notifiable,$notification)
    {
        $message = call_user_func_array([$notification,$this->getToMethod()], [$notifiable]);

        if (is_string($message)) {
            $message = new PushMessage($message);
        }

        return $message;
    }

    /**
     * @return string
     */
    protected function getToMethod()
    {
        return "to" . ucfirst($this->pushServiceName());
    }

    /**
     * @return string
     */
    protected function notificationFor()
    {
        return ucfirst(strtolower($this->pushServiceName()));
    }

    protected abstract function buildData($message);
    protected abstract function pushServiceName();
    protected abstract function extraDataName();
}
