<?php
namespace Edujugon\PushNotification;


use Edujugon\PushNotification\Contracts\PushServiceInterface;

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
    protected $service_collection = [
        'gcm' => Gcm::class,
        'apn' => Apn::class,
        'fcm' => Fcm::class
    ];

    /**
     * Devices' Token where send the notification
     *
     * @var array
     */
    protected $devices_token = [];

    /**
     * data to be sent.
     *
     * @var array
     */
    protected $message = [];

    /**
     * PushNotification constructor.
     * @param PushServiceInterface $service By default GCM
     */
    public function __construct($service = null)
    {
        $this->service = !is_null($service) ? $this->service_collection[$service] : new Fcm;
        var_dump($this->service);
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
     * @param array/string $devices_token
     * @return $this
     */
    public function setDevicesToken($devices_token)
    {
        $this->devices_token = is_array($devices_token) ? $devices_token : array($devices_token);

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
        return $this->service->getUnregisteredDeviceTokens($this->devices_token);
    }

    /**
     * Send Push Notification
     * 
     * @param  \GuzzleHttp\Client client
     * @return boolean true|false
     */
    public function send(){

        return $this->service->send($this->devices_token,$this->message);

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