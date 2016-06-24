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
     * @var json
     */
    protected $feedback = 'Nothing Yet :)';

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
     * @param stdClass $feedback
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
     * Return property if exit otherwise null.
     *
     * @param $property
     * @return null
     */
    public function __get($property){
        return property_exists($this,$property) ? $this->$property : null;
    }

}