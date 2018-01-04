<?php

namespace Edujugon\PushNotification\Channels;

class FcmChannel extends GcmChannel
{
    protected function pushServiceName()
    {
        return 'fcm';
    }
}
