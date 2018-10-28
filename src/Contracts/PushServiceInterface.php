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
    public function setUrl($url);

    /**
     * Set the Push service provider configuration.
     *
     * @param array $config
     * @return mixed
     */
    public function setConfig(array $config);

    /**
     * Set the Push Notification Response.
     *
     * @param $feedback
     * @return mixed
     */
    public function setFeedback($feedback);

    /**
     * Send the notification
     *
     * @param array $deviceTokens
     * @param array $message
     * @return mixed
     */
    public function send(array $deviceTokens, array $message);

    /**
     * Retrieve the device tokes that couldn't receive the message from the push notification.
     *
     * @param array $devices_token
     * @return mixed
     */
    public function getUnregisteredDeviceTokens(array $devices_token);

}