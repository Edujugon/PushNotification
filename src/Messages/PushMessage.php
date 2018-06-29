<?php

namespace Edujugon\PushNotification\Messages;

class PushMessage
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $body;

    /**
     * @var string
     */
    public $sound = 'default';

    /**
     * @var integer
     */
    public $badge;

    /**
     * @var array
     */
    public $extra = [];

    /**
     * @var array
     */
    public $config = [];

    /**
     * Create a new message instance.
     *
     * @param  string  $body
     * @return void
     */
    public function __construct($body = '')
    {
        $this->body = $body;
    }

    /**
     * Set the message body.
     *
     * @param  string $body
     * @return $this
     */
    public function body($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Set the message title.
     *
     * @param  string $title
     * @return $this
     */
    public function title($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set the notification sound.
     *
     * @param  string $sound
     * @return $this
     */
    public function sound($sound)
    {
        $this->sound = $sound;

        return $this;
    }

    /**
     * Set the notification badge.
     *
     * @param  integer $badge
     * @return $this
     */
    public function badge($badge)
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * Set message extra data.
     *
     * @param  array $extra
     * @return $this
     */
    public function extra(array $extra)
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * Set message config.
     *
     * @param  array $config
     * @return $this
     */
    public function config(array $config)
    {
        $this->config = $config;

        return $this;
    }
}
