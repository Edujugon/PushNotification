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
        $message = $this->buildMessage($notifiable, $notification);
        $data = $this->buildData($message);
        $to = $message->to ?? $notifiable->routeNotificationFor($this->notificationFor());

        if (! $to) {
            return;
        }

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

        $this->broadcast();

        return $feedback;
    }

    /**
     * Format the message.
     *
     * @param  mixed $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return \Edujugon\PushNotification\Messages\PushMessage
     */
    protected function buildMessage($notifiable, Notification $notification)
    {
        $message = call_user_func_array([$notification, $this->getToMethod()], [$notifiable]);

        if (is_string($message)) {
            $message = new PushMessage($message);
        }

        return $message;
    }

    /**
     * Get the method name to get the push notification representation of the notification.
     *
     * @return string
     */
    protected function getToMethod()
    {
        return 'to' . ucfirst($this->pushServiceName());
    }

    /**
     * Format push service name for routing notification.
     *
     * @return string
     */
    protected function notificationFor()
    {
        return ucfirst(strtolower($this->pushServiceName()));
    }

    /**
     * Build the push payload data.
     *
     * @param  \Edujugon\PushNotification\Messages\PushMessage $message
     * @return array
     */
    abstract protected function buildData(PushMessage $message);

    /**
     * BroadCast NotificationPushed event
     */
    protected function broadcast()
    {
        if (function_exists('broadcast')) {
            broadcast(new NotificationPushed($this->push));
        } elseif (function_exists('event')) {
            event(new NotificationPushed($this->push));
        }
    }

    /**
     * Get push notification service name.
     *
     * @return string
     */
    abstract protected function pushServiceName();

}
