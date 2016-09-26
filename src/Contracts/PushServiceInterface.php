<?php

namespace Edujugon\PushNotification\Contracts;


interface PushServiceInterface
{

    /**
     * Set the url to connect with the Push service provider.
     * 
     * @param $url
     * @return mixed
     */
    function setUrl($url);

    /**
     * Set the Push service provider configuration.
     *
     * @param array $config
     * @return mixed
     */
    function setConfig(array $config);

    /**
     * Set the Push Notification Response.
     *
     * @param $feedback
     * @return mixed
     */
    function setFeedback($feedback);

    /**
     * Send the notification
     *
     * @param array $deviceTokens
     * @param array $message
     * @return mixed
     */
    function send(array $deviceTokens, array $message);

    /**
     * Retrieve the device tokes that couldn't receive the message from the push notification.
     *
     * @param array $devices_token
     * @return mixed
     */
    function getUnregisteredDeviceTokens(array $devices_token);

}