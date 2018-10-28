<?php
namespace Edujugon\PushNotification;

class PushNotification
{

    /**
     * Push Service Provider
     * @var PushService
     */
    protected $service;

    /**
     * List of the available Push service providers
     *
     * @var PushService[]
     */
    protected $servicesList = [
        'gcm' => Gcm::class,
        'apn' => Apn::class,
        'fcm' => Fcm::class
    ];

    /**
     * The default push service to use.
     *
     * @var string
     */
    private $defaultServiceName = 'gcm';

    /**
     * Devices' Token where send the notification
     *
     * @var array
     */
    protected $deviceTokens = [];

    /**
     * data to be sent.
     *
     * @var array
     */
    protected $message = [];

    /**
     * PushNotification constructor.
     * @param String / a service name of the services list.
     */
    public function __construct($service = null)
    {
        if (!array_key_exists($service, $this->servicesList)) {
            $service = $this->defaultServiceName;
        }
        
        $this->service = is_null($service) ? new $this->servicesList[$this->defaultServiceName]
                                            : new $this->servicesList[$service];
    }

    /**
     * Set the Push Service to be used.
     *
     * @param $serviceName
     * @return $this
     */
    public function setService($serviceName)
    {
        if (!array_key_exists($serviceName, $this->servicesList)) {
            $serviceName = $this->defaultServiceName;
        }

        $this->service = new $this->servicesList[$serviceName];

        return $this;
    }

    /**
     * Set the message of the notification.
     *
     * @param array $data
     * @return $this
     */
    public function setMessage(array $data)
    {
        $this->message = $data;

        return $this;
    }


    /**
     * @param array/string $deviceTokens
     * @return $this
     */
    public function setDevicesToken($deviceTokens)
    {
        $this->deviceTokens = is_array($deviceTokens) ? $deviceTokens : array($deviceTokens);

        return $this;
    }

    /**
     * @param string $api_key
     * @return $this
     */
    public function setApiKey($api_key)
    {
        // if apn doesn't do anything
        if (!$this->service instanceof Apn) {
            $this->service->setApiKey($api_key);
        }

        return $this;
    }

    /**
     * Set the Push service configuration
     *
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->service->setConfig($config);

        return $this;
    }

    /**
     * Set the Push service url
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->service->setUrl($url);
        return $this;
    }

    /**
     *Get the unregistered tokens of the notification sent.
     *
     * @return array $tokenUnRegistered
     */
    public function getUnregisteredDeviceTokens()
    {
        return $this->service->getUnregisteredDeviceTokens($this->deviceTokens);
    }

    /**
     * Give the Push Notification Feedback after sending a notification.
     *
     * @return mixed
     */
    public function getFeedback()
    {
        return $this->service->feedback;
    }

    /**
     * Send Push Notification
     *
     * @return $this
     */
    public function send()
    {
        $this->service->send($this->deviceTokens, $this->message);

        return $this;
    }

    /**
     * @param $topic
     * @param $isCondition
     * @return $this
     */
    public function sendByTopic($topic, $isCondition = false)
    {
        if ($this->service instanceof Fcm) {
            $this->service->sendByTopic($topic, $this->message, $isCondition);
        }

        return $this;
    }

    /**
     * Return property if exit here or in service object, otherwise null.
     *
     * @param $property
     * @return mixed / null
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        if (property_exists($this->service, $property)) {
            return $this->service->$property;
        }

        return null;
    }
}
