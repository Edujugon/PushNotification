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
     * @param $message
     * @param bool $isCondition
     * @return object
     */
    public function sendByTopic($topic, $message, $isCondition = false)
    {
        $headers = $this->addRequestHeaders();
        $data = $this->buildData($topic, $message, $isCondition);

        try {
            $result = $this->client->post(
                $this->url,
                [
                    'headers' => $headers,
                    'json' => $data,
                ]
            );

            $json = $result->getBody();

            $this->setFeedback(json_decode($json, false, 512, JSON_BIGINT_AS_STRING));

        } catch (\Exception $e) {
            $response = ['success' => false, 'error' => $e->getMessage()];

            $this->setFeedback(json_decode(json_encode($response)));

        } finally {
            return $this->feedback;
        }
    }

    /**
     * Prepare the data to be sent
     *
     * @param $topic
     * @param $message
     * @param $isCondition
     * @return array
     */
    protected function buildData($topic, $message, $isCondition)
    {
        $condition = $isCondition ? ['condition' => $topic] : ['to' => '/topics/' . $topic];

        return array_merge($condition, $this->buildMessage($message));
    }
}