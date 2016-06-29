<?php
namespace Edujugon\PushNotification;

use Edujugon\PushNotification\Contracts\PushServiceInterface;

class Apn extends PushService implements PushServiceInterface
{

    /**
     * Gcm constructor.
     */
    public function __construct()
    {
        $this->url = 'ssl://gateway.sandbox.push.apple.com:2195';
        //production: gateway.push.apple.com

        $this->config = $this->initializeConfig('apn');
    }

    private function fillBody()
    {
        return [
            'aps' => [
                'alert' => [
                    'title' => 'This is the title',
                    'body' => 'This is the body'
                ],
                'sound' => 'default'

            ],
            'extraPayLoad' => [
                'title' => 'This is the title',
                'body' => 'This is the body',
            ]
        ];
    }

    /**
     * Compose the feedback array
     * @return array
     */
    private function initializeFeedback()
    {
        return ['success' => true,
            'success' => 0,
            'failure' => 0,
            'tokenFailList' => []
        ];
    }

    /**
     * Provide the unregistered tokens of the notification sent.
     *
     * @param array $devices_token
     * @return array
     */
    public function getUnregisteredDeviceTokens(array $devices_token) : array
    {
        if(! empty($this->feedback->tokenFailList))
            return $this->feedback->tokenFailList;
        else return [];
    }

    /**
     * Check if the certificate file exist.
     * @return bool
     */
    private function existCertificate()
    {
        $certificate = $this->config['certificate'];
        if(!file_exists($certificate))
        {
            $response = ['success' => false, 'error' => "Please, add your APN certificate to: $certificate" . PHP_EOL];

            $this->setFeedback(json_decode(json_encode($response), FALSE));

            return false;
        }
        return true;
    }
    /**
     * Create the connection to APNS server
     * If some error, the error is stored in class feedback property.
     * IF OKAY, return connection
     *
     * @return bool|resource
     */
    private function openConnectionAPNS()
    {

        if(!$this->existCertificate()) return false;

        $certificate = $this->config['certificate'];
        $passphrase = $this->config['passPhrase'];

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $certificate);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        // Open a connection to the APNS server
        $fp = stream_socket_client(
            $this->url, $err,
            $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$fp)
        {
            $response = ['success' => false, 'error' => "Failed to connect: $err $errstr" . PHP_EOL];

            $this->setFeedback(json_decode(json_encode($response), FALSE));

            return false;
        }
        return $fp;
    }

    /**
     * Send Push Notification
     * @param \GuzzleHttp\Client client
     * @param  array $deviceTokens
     * @param array $message
     * @return \stdClass  GCM Response
     */
    public function send(array $deviceTokens,array $message) : \stdClass
    {

        $fp = $this->openConnectionAPNS();
        if(!$fp) return $this->feedback;

        // Encode the payload as JSON
        $payload = json_encode($message);

        $feedback = $this->initializeFeedback();

        foreach ($deviceTokens as $token)
        {
            // Build the binary notification
            //Check if the token is numeric no to get PHP Warnings with pack function.
            if (ctype_xdigit($token))  {
                $msg = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
            }else
            {
                $feedback['tokenFailList'][] = $token;
                $feedback['failure'] += 1;
                continue;
            }


            // Send it to the server
            $result = fwrite($fp, $msg, strlen($msg));

            if (!$result)
            {
                $feedback['tokenFailList'][] = $token;
                $feedback['failure'] += 1;

            }else
                $feedback['success'] += 1;

        }

        $this->setFeedback(json_decode(json_encode($feedback), FALSE));

        // Close the connection to the server
        fclose($fp);

        return $this->feedback;

    }
}