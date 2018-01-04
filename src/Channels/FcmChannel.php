<?php

namespace Edujugon\PushNotification\Channels;

class FcmChannel extends GcmChannel
{
    /**
     * {@inheritdoc}
     */
    protected function pushServiceName()
    {
        return 'fcm';
    }
}
