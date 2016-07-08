<?php
namespace Edujugon\PushNotification;


class PushNotification
{

    /**
     * Push Service Provider
     * @var
     */
    protected $service;

    /**
     * List of the available Push service providers
     *
     * @var array
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
        if(!array_key_exists($service,$this->servicesList)) $service = $this->defaultServiceName;
        
        $this->service = is_null($service) ? new $this->servicesList[$this->defaultServiceName]
                                            : new $this->servicesList[$service];

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
        $this->service->setApiKey($api_key) ;

        return $this;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->service->setConfig($config);

        return $this;
    }

    /**
     *Provide the unregistered tokens of the notification sent.
     * 
     * @return array $tokenUnRegistered
     */
    public function getUnregisteredDeviceTokens()
    {
        return $this->service->getUnregisteredDeviceTokens($this->deviceTokens);
    }

    /**
     * Send Push Notification
     * 
     * @param  \GuzzleHttp\Client client
     * @return boolean true|false
     */
    public function send(){

        return $this->service->send($this->deviceTokens,$this->message);

    }

    /**
     * Return property if exit here or in service property, otherwise null.
     *
     * @param $property
     * @return null
     */
    public function __get($property){

        if(property_exists($this,$property))
        {
            return $this->$property;
        }

        if(property_exists($this->service,$property))
        {
            return $this->service->$property;
        }

        return null;

    }
}