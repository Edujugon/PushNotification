<?php

namespace Edujugon\PushNotification\Contracts;


interface PushServiceInterface
{

    function setUrl($url);

    function setApiKey($api_key);

    function setConfig(array $config);

    function setFeedback($feedback);

    function send(array $deviceTokens, array $message);

    function getUnregisteredDeviceTokens(array $devices_token);

}