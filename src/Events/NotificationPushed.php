<?php

namespace Edujugon\PushNotification\Events;

use Edujugon\PushNotification\PushNotification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationPushed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \Edujugon\PushNotification\PushNotification
     */
    public $push;

    /**
     * Create a new event instance.
     *
     * @param  \Edujugon\PushNotification\PushNotification $push
     */
    public function __construct(PushNotification $push)
    {
        $this->push = $push;
    }
}
