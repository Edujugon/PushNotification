<?php
namespace Edujugon\PushNotification;

use Edujugon\PushNotification\Exceptions\PushNotificationException;

abstract class PushService
{

    /**
     * Server Url for push notification server
     *
     * @var string
     */
    protected $url = '';

    /**
     * Config details
     * By default priority is set to high and dry_run to false
     *
     * @var array
     */
    protected $config = [];

    /**
     * Push Server Response
     * @var object
     */
    protected $feedback;

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param object $feedback
     */
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * Update the values by key on config array from the passed array. If any key doesn't exist, it's added.
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = array_replace($this->config, $config);
    }

    /**
     * Initialize the configuration for the chosen push service // gcm,etc..
     *
     * @param $service
     *
     * @throws PushNotificationException
     *
     * @return mixed
     */
    public function initializeConfig($service)
    {
        if (function_exists('config_path') &&
            file_exists(config_path('pushnotification.php')) &&
            function_exists('app')
        ) {
            $configuration = app('config')->get('pushnotification');
        } else {
            $configuration = include(__DIR__ . '/Config/config.php');
        }

        if (!array_key_exists($service, $configuration)) {
            throw new PushNotificationException("Service '$service' missed in config/pushnotification.php");
        }
        return $configuration[$service];
    }

    /**
     * Initialize the feedback array
     * @return array
     */
    protected function initializeFeedback()
    {
        return [
            'success' => 0,
            'failure' => 0,
            'tokenFailList' => []
        ];
    }

    /**
     * Return property if exit otherwise null.
     *
     * @param $property
     * @return mixed|null
     */
    public function __get($property)
    {
        return property_exists($this, $property) ? $this->$property : null;
    }

}