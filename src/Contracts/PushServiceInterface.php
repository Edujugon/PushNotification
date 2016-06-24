<?php

namespace Edujugon\PushNotification\Contracts;


interface PushServiceInterface
{

    function setUrl($url);

    function setApiKey($api_key);

    function setConfig(array $config);

    function send(\GuzzleHttp\Client $client, array $deviceTokens, array $message);

}