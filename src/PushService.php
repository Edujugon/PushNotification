<?php

namespace Edujugon\PushNotification;


abstract class PushService
{

    /**
     * Server Url for push hosting service
     * By default GCM
     *
     * @var string
     */
    protected $url = '';

    /**
     * Confing details
     * By default priority is set to high and dry_run to false
     *
     * @var array
     */
    protected $config = [];

    /**
     * Server API Key to interact with Push server
     *
     * @var string
     */
    protected $api_key = '';

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
     * @param string $api_key
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
    }

    /**
     * @param object $feedback
     */
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * Update the values by key on config array from the passed array.
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = array_replace($this->config,$config);
    }

    /**
     * Initialize the configuration for the chosen push service // gcm,etc..
     * Check if config_path exist as function 
     * 
     * @param $service
     * @return mixed
     */
    public function initializeConfig($service)
    {
        if(function_exists('config_path'))
        {
            $configuration = include(config_path('pushnotification.php'));
        }else
        {
            $configuration = include(__DIR__ . '/Config/config.php');
        }
        
        return $configuration[$service];
    }

    /**
     * Return property if exit otherwise null.
     *
     * @param $property
     * @return null
     */
    public function __get($property){
        return property_exists($this,$property) ? $this->$property : null;
    }

}