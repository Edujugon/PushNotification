<?php

namespace Edujugon\PushNotification;

use Carbon\Carbon;
use Edujugon\PushNotification\Fcm;
use Exception;
use Google\Client as GoogleClient;
use Google\Service\FirebaseCloudMessaging;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FcmV1 extends Fcm
{
    const CACHE_SECONDS = 55 * 60; // 55 minutes

    protected $unregisteredDeviceTokens = [];

    /**
     * Fcm constructor.
     * Override parent constructor.
     */
    public function __construct()
    {
        $this->config = $this->initializeConfig('fcmv1');

        $this->url = 'https://fcm.googleapis.com/v1/projects/' . $this->config['projectId'] . '/messages:send';

        $this->client = new Client($this->config['guzzle'] ?? []);
    }

    /**
     * Set the apiKey for the notification
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        throw new Exception('Not available on FCM V1');
    }

    /**
     * Set the projectId for the notification
     * @param string $projectId
     */
    public function setProjectId($projectId)
    {
        $this->config['projectId'] = $projectId;

        $this->url = 'https://fcm.googleapis.com/v1/projects/' . $this->config['projectId'] . '/messages:send';
    }

    /**
     * Set the jsonFile path for the notification
     * @param string $jsonFile
     */
    public function setJsonFile($jsonFile)
    {
        $this->config['jsonFile'] = $jsonFile;
    }

    /**
     * Set the needed headers for the push notification.
     *
     * @return array
     */
    protected function addRequestHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->getOauthToken(),
            'Content-Type' =>  'application/json',
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
        // FCM v1 does not allows multiple devices at once

        $headers = $this->addRequestHeaders();
        $jsonData = ['message' => $this->buildMessage($message)];

        $feedbacks = [];
        $this->unregisteredDeviceTokens = [];

        foreach ($deviceTokens as $deviceToken) {
            try {
                $jsonData['message']['token'] = $deviceToken;

                $result = $this->client->post(
                    $this->url,
                    [
                        'headers' => $headers,
                        'json' => $jsonData,
                    ]
                );

                $json = $result->getBody();

                $feedbacks[$deviceToken] = [
                    'success' => true,
                    'response' => json_decode($json, true, 512, JSON_BIGINT_AS_STRING),
                ];
            } catch (ClientException $e) {
                $feedbacks[$deviceToken] = [
                    'success' => false,
                    'error' => json_decode($e->getResponse()->getBody()->getContents(), true),
                ];

                $this->unregisteredDeviceTokens[] = $deviceToken;
            } catch (\Exception $e) {
                $feedbacks[$deviceToken] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];

                $this->unregisteredDeviceTokens[] = $deviceToken;
            }
        }

        $this->setFeedback($feedbacks);
    }

    /**
     * Provide the unregistered tokens of the sent notification.
     *
     * @param array $devices_token
     * @return array $tokenUnRegistered
     */
    public function getUnregisteredDeviceTokens(array $devices_token)
    {
        return $this->unregisteredDeviceTokens;
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

        return [
            'message' => array_merge($condition, $this->buildMessage($message)),
        ];
    }

    protected function getOauthToken()
    {
        return Cache::remember(
            Str::slug('fcm-v1-oauth-token-' . $this->config['projectId']),
            Carbon::now()->addSeconds(self::CACHE_SECONDS),
            function () {
                $jsonFilePath = $this->config['jsonFile'];

                $googleClient = new GoogleClient();

                $googleClient->setAuthConfig($jsonFilePath);
                $googleClient->addScope(FirebaseCloudMessaging::FIREBASE_MESSAGING);

                $accessToken = $googleClient->fetchAccessTokenWithAssertion();

                $oauthToken = $accessToken['access_token'];

                return $oauthToken;
            }
        );
    }
}
