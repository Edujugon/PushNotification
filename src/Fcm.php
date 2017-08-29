<?php

namespace Edujugon\PushNotification;


use GuzzleHttp\Client;

class Fcm extends Gcm
{

    /**
     * Fcm constructor.
     * Override parent constructor.
     */
    public function __construct()
    {
        $this->url = 'https://fcm.googleapis.com/fcm/send';

        $this->config = $this->initializeConfig('fcm');

        $this->client = new Client();
    }

    /**
     * Send notification by topic.
     * if isCondition is true, $topic will be treated as an expression
     *
     * @param $topic
     * @param bool $isCondition
     * @return object
     */
    public function sendByTopic($topic, $isCondition = false)
    {
        $headers = $this->addRequestHeaders();

        $data = $this->buildData($topic, $isCondition);

        try {
            $result = $this->client->post(
                $this->url,
                [
                    'headers' => $headers,
                    'json' => $data,
                ]
            );

            $json = $result->getBody();

            $this->setFeedback(json_decode($json));

        } catch (\Exception $e) {
            $response = ['success' => false, 'error' => $e->getMessage()];

            $this->setFeedback(json_decode(json_encode($response), FALSE));

        } finally {
            return $this->feedback;
        }
    }

    /**
     * Prepare the data to be sent
     *
     * @param $topic
     * @param $isCondition
     * @return array
     */
    protected function buildData($topic, $isCondition)
    {
        if (!$isCondition) {

            return [
                'to' => '/topics/' . $topic,
                'data' => $this->message
            ];

        } else {
            return [
                'condition' => $topic,
                'data' => $this->message
            ];
        }
    }
}