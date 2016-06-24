<?php

namespace Edujugon\PushNotification\Contracts;


interface PushServiceInterface
{

    function setUrl($url);

    function setApiKey($api_key);

    function setConfig(array $config);

    function send($client, array $deviceTokens, array $message);

}