<?php
namespace Edujugon\PushNotification;

use Edujugon\PushNotification\Contracts\PushServiceInterface;
use GuzzleHttp\Client;

class Gcm extends PushService implements PushServiceInterface
{

    /**
     * Set the apiKey for the notification
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->config['apiKey'] = $apiKey;
    }

    /**
     * Client to do the request
     *
     * @var \GuzzleHttp\Client $client
     */
    protected $client;

    /**
     * Gcm constructor.
     */
    public function __construct()
    {
        $this->url = 'https://android.googleapis.com/gcm/send';
        
        $this->config = $this->initializeConfig('gcm');
        $this->client = new Client;
    }

    /**
     * Provide the unregistered tokens of the sent notification.
     *
     * @param array $devices_token
     * @return array $tokenUnRegistered
     */
    public function getUnregisteredDeviceTokens(array $devices_token)
    {
        /**
         * If there is any failure sending the notification
         */
        if ($this->feedback && isset($this->feedback->failure)) {
            $unRegisteredTokens = $devices_token;

            /**
             * Walk the array looking for any error.
             * If no error, unset it from all token list which will become the unregistered tokens array.
             */
            foreach ($this->feedback->results as $key => $message) {
                if (!isset($message->error)) {
                    unset($unRegisteredTokens[$key]);
                }
            }

            return $unRegisteredTokens;
        }

        return [];
    }

    /**
     * Set the needed fields for the push notification
     *
     * @param $deviceTokens
     * @param $message
     * @return array
     */
    protected function addRequestFields($deviceTokens, $message)
    {

        $params = $this->cleanConfigParams();

        $message = $this->buildMessage($message);

        return array_merge($params, $message, ['registration_ids'  => $deviceTokens]);
    }

    /**
     * @param $message
     * @return array
     */
    protected function buildMessage($message)
    {
        // if no notification nor data keys, then set Data Message as default.
        if (!array_key_exists('data', $message) && !array_key_exists('notification', $message)) {
            return ['data' => $message];
        }

        return $message;
    }

    /**
     * Clean the config params from unnecessary params no to make the notification too heavy.
     *
     * @return array
     */
    private function cleanConfigParams()
    {
        /**
         * Add the params you want to be removed from the push notification
         */
        $paramsToBeRemoved = ['apiKey'];

        return array_filter($this->config, function ($key) use ($paramsToBeRemoved) {
            return !in_array($key, $paramsToBeRemoved);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Set the needed headers for the push notification.
     *
     * @return array
     */
    protected function addRequestHeaders()
    {
        return [
            'Authorization' => 'key=' . $this->config['apiKey'],
            'Content-Type:' =>'application/json'
        ];
    }

    /**
     * Send Push Notification
     *
     * @param  array $deviceTokens
     * @param array $message
     *
     * @return \stdClass  GCM Response
     */
    public function send(array $deviceTokens, array $message)
    {

        $fields = $this->addRequestFields($deviceTokens, $message);
        $headers = $this->addRequestHeaders();

        try {
            $result = $this->client->post(
                $this->url,
                [
                    'headers' => $headers,
                    'json' => $fields,
                ]
            );

            $json = $result->getBody();

            $this->setFeedback(json_decode($json, false, 512, JSON_BIGINT_AS_STRING));

            return $this->feedback;

        } catch (\Exception $e) {
            $response = ['success' => false, 'error' => $e->getMessage()];
            
            $this->setFeedback(json_decode(json_encode($response)));

            return $this->feedback;
        }
    }
}
