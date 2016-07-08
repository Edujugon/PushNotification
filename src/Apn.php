<?php
namespace Edujugon\PushNotification;

use Edujugon\PushNotification\Contracts\PushServiceInterface;

class Apn extends PushService implements PushServiceInterface
{

    /**
     * Url for development purposes
     *
     * @var string
     */
    private $sandboxUrl = 'ssl://gateway.sandbox.push.apple.com:2195';

    /**
     * Url for production
     *
     * @var string
     */
    private $productionUrl = 'ssl://gateway.push.apple.com:2195';

    /**
     * Apn constructor.
     */
    public function __construct()
    {
        $this->url = $this->productionUrl;

        $this->config = $this->initializeConfig('apn');
    }

    /**
     * Call parent method.
     * Check if there is dry_run parameter in config data. Set the service url according to the dry_run value.
     *
     * @param array $config
     */
    public function setConfig(array $config)
    {
        parent::setConfig($config);

        if(isset($this->config['dry_run']))
        {
            if($this->config['dry_run']){

                $this->setUrl($this->sandboxUrl);

            }else $this->setUrl($this->productionUrl);
        }
    }

    /**
     * Provide the unregistered tokens of the notification sent.
     *
     * @param array $devices_token
     * @return array
     */
    public function getUnregisteredDeviceTokens(array $devices_token)
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
            $response = ['success' => false, 'error' => "Please, add your APN certificate to your service configuration file." . PHP_EOL];

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
     * @param  array $deviceTokens
     * @param array $message
     * @return \stdClass  GCM Response
     */
    public function send(array $deviceTokens,array $message)
    {
        
        if(!$this->existCertificate()) return $this->feedback;

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


            // Send the notification to the server
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