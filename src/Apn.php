<?php
namespace Edujugon\PushNotification;

use Edujugon\PushNotification\Contracts\PushServiceInterface;
use Illuminate\Support\Arr;

class Apn extends PushService implements PushServiceInterface
{
    const APNS_DEVELOPMENT_SERVER = 'https://api.development.push.apple.com';
    const APNS_PRODUCTION_SERVER = 'https://api.push.apple.com';
    const APNS_PORT = 443;
    const APNS_PATH_SCHEMA = '/3/device/{token}';

    /**
     * Number of concurrent requests to multiplex in the same connection.
     *
     * @var int
     */
    private $nbConcurrentRequests = 20;

    /**
     * Number of maximum concurrent connections established to the APNS servers.
     *
     * @var int
     */
    private $maxConcurrentConnections = 1;

    /**
     * Flag to know if we should automatically close connections to the APNS servers or keep them alive.
     *
     * @var bool
     */
    private $autoCloseConnections = true;

    /**
     * Current curl_multi handle instance.
     *
     * @var resource
     */
    private $curlMultiHandle;

    /**
     * Apn constructor.
     */
    public function __construct()
    {
        if (!defined('CURL_HTTP_VERSION_2')) {
            define('CURL_HTTP_VERSION_2', 3);
        }

        $this->url = self::APNS_PRODUCTION_SERVER;

        $this->config = $this->initializeConfig('apn');
    }

    /**
     * Provide the unregistered tokens of the notification sent.
     *
     * @param array $devices_token
     * @return array
     */
    public function getUnregisteredDeviceTokens(array $devices_token)
    {
        $tokens = [];

        if (!empty($this->feedback->tokenFailList)) {
            $tokens = $this->feedback->tokenFailList;
        }
        if (!empty($this->feedback->apnsFeedback)) {
            $tokens = array_merge($tokens, Arr::pluck($this->feedback->apnsFeedback, 'devtoken'));
        }

        return $tokens;
    }

    /**
     * Send Push Notification
     * @param  array $deviceTokens
     * @param array $message
     * @return \stdClass  APN Response
     */
    public function send(array $deviceTokens, array $message)
    {
        if (false == $this->existCertificate()) {
            return $this->feedback;
        }

        $responseCollection = [
            'success' => true,
            'error' => '',
            'results' => [],
        ];

        if (!$this->curlMultiHandle) {
            $this->curlMultiHandle = curl_multi_init();

            if (!defined('CURLPIPE_MULTIPLEX')) {
                define('CURLPIPE_MULTIPLEX', 2);
            }

            curl_multi_setopt($this->curlMultiHandle, CURLMOPT_PIPELINING, CURLPIPE_MULTIPLEX);
            if (defined('CURLMOPT_MAX_HOST_CONNECTIONS')) {
                curl_multi_setopt($this->curlMultiHandle, CURLMOPT_MAX_HOST_CONNECTIONS, $this->maxConcurrentConnections);
            }
        }

        $mh = $this->curlMultiHandle;
        $errors = [];

        $i = 0;
        while (!empty($deviceTokens) && $i++ < $this->nbConcurrentRequests) {
            $deviceToken = array_pop($deviceTokens);
            curl_multi_add_handle($mh, $this->prepareHandle($deviceToken, $message));
        }

        // Clear out curl handle buffer
        do {
            $execrun = curl_multi_exec($mh, $running);
        } while ($execrun === CURLM_CALL_MULTI_PERFORM);

        // Continue processing while we have active curl handles
        while ($running > 0 && $execrun === CURLM_OK) {
            // Block until data is available
            $select_fd = curl_multi_select($mh);
            // If select returns -1 while running, wait 250 microseconds before continuing
            // Using curl_multi_timeout would be better but it isn't available in PHP yet
            // https://php.net/manual/en/function.curl-multi-select.php#115381
            if ($running && $select_fd === -1) {
                usleep(250);
            }

            // Continue to wait for more data if needed
            do {
                $execrun = curl_multi_exec($mh, $running);
            } while ($execrun === CURLM_CALL_MULTI_PERFORM);

            // Start reading results
            while ($done = curl_multi_info_read($mh)) {
                $handle = $done['handle'];

                $result = curl_multi_getcontent($handle);

                // find out which token the response is about
                $token = curl_getinfo($handle, CURLINFO_PRIVATE);

                $responseParts = explode("\r\n\r\n", $result, 2);
                $headers = '';
                $body = '';
                if (isset($responseParts[0])) {
                    $headers = $responseParts[0];
                }
                if (isset($responseParts[1])) {
                    $body = $responseParts[1];
                }

                $statusCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
                if ($statusCode === 0) {
                    $responseCollection['success'] = false;

                    $responseCollection['error'] = [
                        'status' => $statusCode,
                        'headers' => $headers,
                        'body' => curl_error($handle),
                        'token' => $token
                    ];
                    continue;
                }

                $responseCollection['success'] = $responseCollection['success'] && $statusCode == 200;

                $responseCollection['results'][] = [
                    'status' => $statusCode,
                    'headers' => $headers,
                    'body' => (string)$body,
                    'token' => $token
                ];
                curl_multi_remove_handle($mh, $handle);
                curl_close($handle);

                if (!empty($deviceTokens)) {
                    $deviceToken = array_pop($deviceTokens);
                    curl_multi_add_handle($mh, $this->prepareHandle($deviceToken, $message));
                    $running++;
                }
            }
        }

        if ($this->autoCloseConnections) {
            curl_multi_close($mh);
            $this->curlMultiHandle = null;
        }

        //Set the global feedback
        $this->setFeedback(json_decode(json_encode($responseCollection)));

        return $responseCollection;
    }

    /**
     * Get Url for APNs production server.
     *
     * @param Notification $notification
     * @return string
     */
    private function getProductionUrl(string $deviceToken)
    {
        return self::APNS_PRODUCTION_SERVER . $this->getUrlPath($deviceToken);
    }

    /**
     * Get Url for APNs sandbox server.
     *
     * @param Notification $notification
     * @return string
     */
    private function getSandboxUrl(string $deviceToken)
    {
        return self::APNS_DEVELOPMENT_SERVER . $this->getUrlPath($deviceToken);
    }

    /**
     * Get Url path.
     *
     * @param Notification $notification
     * @return mixed
     */
    private function getUrlPath(string $deviceToken)
    {
        return str_replace("{token}", $deviceToken, self::APNS_PATH_SCHEMA);
    }

    /**
     * Decorate headers
     *
     * @return array
     */
    public function decorateHeaders(array $headers): array
    {
        $decoratedHeaders = [];
        foreach ($headers as $name => $value) {
            $decoratedHeaders[] = $name . ': ' . $value;
        }
        return $decoratedHeaders;
    }

    /**
     * @param $token
     * @param array $message
     * @param $request
     * @param array $deviceTokens
     */
    public function prepareHandle($deviceToken, array $message)
    {
        $uri = false === $this->config['dry_run'] ? $this->getProductionUrl($deviceToken) : $this->getSandboxUrl($deviceToken);
        $headers = $message['headers'] ?? [];
        if (isset($message['headers'])) {
            unset($message['headers']);
        }
        $body = json_encode($message);

        $config = $this->config;

        $options = [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2,
            CURLOPT_URL => $uri,
            CURLOPT_PORT => self::APNS_PORT,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HEADER => true,

            CURLOPT_SSLCERT        => $config['certificate'],
            CURLOPT_SSL_VERIFYPEER => true
        ];

        if (isset($config['passPhrase'])) {
            $options[CURLOPT_SSLCERTPASSWD] = $config['passPhrase'];
        }

        $ch = curl_init();

        curl_setopt_array($ch, $options);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->decorateHeaders($headers));
        }

        // store device token to identify response
        curl_setopt($ch, CURLOPT_PRIVATE, $deviceToken);

        return $ch;
    }

    /**
     * Set the feedback with no exist any certificate.
     *
     * @return mixed|void
     */
    private function messageNoExistCertificate()
    {
        $response = [
            'success' => false,
            'error' => "Please, add your APN certificate to the iosCertificates folder." . PHP_EOL
        ];

        $this->setFeedback(json_decode(json_encode($response)));
    }

    /**
     * Check if the certificate file exist.
     * @return bool
     */
    private function existCertificate()
    {
        if (isset($this->config['certificate'])) {
            $certificate = $this->config['certificate'];
            if (!file_exists($certificate)) {
                $this->messageNoExistCertificate();
                return false;
            }

            return true;
        }

        $this->messageNoExistCertificate();
        return false;
    }

}