<?php
namespace Edujugon\PushNotification;

use Edujugon\PushNotification\Contracts\PushServiceInterface;
use Illuminate\Support\Arr;

class Apn extends PushService implements PushServiceInterface
{

    const MAX_ATTEMPTS = 3;

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
     * Feedback SandBox url
     * @var string
     */
    private $feedbackSandboxUrl = 'ssl://feedback.sandbox.push.apple.com:2196';

    /**
     * Feedback Production url
     * @var string
     */
    private $feedbackProductionUrl = 'ssl://feedback.push.apple.com:2196';

    /**
     *  It's dynamically filled based on the dry_run parameter.
     *
     * @var string
     */
    private $feedbackUrl;

    /**
     * The number of attempts to re-try before failing.
     * Set to zero for unlimited attempts.
     *
     * @var int
     */
    private $maxAttempts = self::MAX_ATTEMPTS;

    private $attempts = 0;

    /**
     * Apn constructor.
     */
    public function __construct()
    {
        $this->url = $this->productionUrl;

        $this->config = $this->initializeConfig('apn');

        $this->setProperGateway();
    }

    /**
     * Call parent method.
     * Check if there is dry_run parameter in config data. Set the service url according to the dry_run value.
     *
     * @param array $config
     * @return void
     */
    public function setConfig(array $config)
    {
        parent::setConfig($config);

        $this->setProperGateway();
        $this->setRetryAttemptsIfConfigured();
    }

    /**
     *Set the correct Gateway url and the Feedback url based on dry_run param.
     *
     * @return void
     */
    private function setProperGateway()
    {
        if (isset($this->config['dry_run'])) {
            if ($this->config['dry_run']) {
                $this->setUrl($this->sandboxUrl);
                $this->feedbackUrl = $this->feedbackSandboxUrl;

            } else {
                $this->setUrl($this->productionUrl);
                $this->feedbackUrl = $this->feedbackProductionUrl;
            }
        }
    }

    /**
     * Configure re-try attempts.
     *
     * @return void
     */
    private function setRetryAttemptsIfConfigured()
    {
        if (isset($this->config['connection_attempts']) &&
            is_numeric($this->config['connection_attempts'])) {
            $this->maxAttempts = $this->config['connection_attempts'];
        }
    }

    /**
     * Determines whether the connection attempts should be unlimited.
     *
     * @return bool
     */
    private function isUnlimitedAttempts()
    {
        return $this->maxAttempts == 0;
    }

    /**
     * Check if can retry a connection
     *
     * @return bool
     */
    private function canRetry()
    {
        if ($this->isUnlimitedAttempts()) {
            return true;
        }

        $this->attempts++;

        return $this->attempts < $this->maxAttempts;
    }

    /**
     * Reset connection attempts
     *
     * @return $this
     */
    private function resetAttempts()
    {
        $this->attempts = 0;

        return $this;
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

    /**
     * Compose the stream socket
     *
     * @return resource
     */
    private function composeStreamSocket()
    {
        $ctx = stream_context_create();

        //Already checked if certificate exists.
        $certificate = $this->config['certificate'];
        stream_context_set_option($ctx, 'ssl', 'local_cert', $certificate);

        if (isset($this->config['passPhrase'])) {
            $passPhrase = $this->config['passPhrase'];
            if (!empty($passPhrase)) {
                stream_context_set_option($ctx, 'ssl', 'passphrase', $passPhrase);
            }
        }

        if (isset($this->config['passFile'])) {
            $passFile = $this->config['passFile'];
            if (file_exists($passFile)) {
                stream_context_set_option($ctx, 'ssl', 'local_pk', $passFile);
            }
        }

        return $ctx;
    }

    /**
     * Create the connection to APNS server
     * If some error, the error is stored in class feedback property.
     * IF OKAY, return connection
     *
     * @return bool|resource
     */
    private function openConnectionAPNS($ctx)
    {
        $fp = false;

        // Open a connection to the APNS server
        try {
            $fp = stream_socket_client(
                $this->url,
                $err,
                $errstr,
                60,
                STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT,
                $ctx
            );

            stream_set_blocking($fp, 0);

            if (!$fp) {
                $response = ['success' => false, 'error' => "Failed to connect: $err $errstr" . PHP_EOL];

                $this->setFeedback(json_decode(json_encode($response)));

            }

        } catch (\Exception $e) {
            //if stream socket can't be established, try again
            if ($this->canRetry()) {
                return $this->openConnectionAPNS($ctx);
            }

            $response = ['success' => false, 'error' => 'Connection problem: ' . $e->getMessage() . PHP_EOL];
            $this->setFeedback(json_decode(json_encode($response)));

        } finally {
            $this->resetAttempts();
            return $fp;
        }
    }

    /**
     * Send Push Notification
     * @param  array $deviceTokens
     * @param array $message
     * @return \stdClass  APN Response
     */
    public function send(array $deviceTokens, array $message)
    {

        /**
         * If there isn't certificate returns the feedback.
         * Feedback has been loaded in existCertificate method if no certificate found
         */
        if (!$this->existCertificate()) {
            return $this->feedback;
        }

        // Encode the payload as JSON
        $payload = json_encode($message);

        //When sending a notification we prepare a clean feedback
        $feedback = $this->initializeFeedback();

        foreach ($deviceTokens as $token) {
            /**
             * Open APN connection
             */
            $ctx = $this->composeStreamSocket();

            $fp = $this->openConnectionAPNS($ctx);
            if (!$fp) {
                return $this->feedback;
            }


            // Build the binary notification
            //Check if the token is numeric not to get PHP Warnings with pack function.
            if (ctype_xdigit($token)) {
                $msg = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
            } else {
                $feedback['tokenFailList'][] = $token;
                $feedback['failure'] += 1;
                continue;
            }

            $result = fwrite($fp, $msg, strlen($msg));

            if (!$result) {
                $feedback['tokenFailList'][] = $token;
                $feedback['failure'] += 1;

            } else {
                $feedback['success'] += 1;
            }

            // Close the connection to the server
            if ($fp) {
                fclose($fp);
            }

        }

        /**
         * Retrieving the apn feedback
         */
        $apnsFeedback = $this->apnsFeedback();

        /**
         * Merge the apn feedback to our custom feedback if there is any.
         */
        if (!empty($apnsFeedback)) {
            $feedback = array_merge($feedback, $apnsFeedback);

            $feedback = $this->updateCustomFeedbackValues($apnsFeedback, $feedback, $deviceTokens);
        }

        //Set the global feedback
        $this->setFeedback(json_decode(json_encode($feedback)));

        return $this->feedback;
    }

    /**
     * Get the unregistered device tokens from the apns feedback.
     * Connect to apn server in order to collect the tokens of the apps which were removed from the device.
     *
     * @return array
     */
    public function apnsFeedback() {

        $feedback_tokens = array();

        if (!$this->existCertificate()) {
            return $feedback_tokens;
        }

        //connect to the APNS feedback servers
        $ctx = $this->composeStreamSocket();

        // Open a connection to the APNS server
        try {
            $apns = stream_socket_client($this->feedbackUrl, $errcode, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);

            //Read the data on the connection:
            while (!feof($apns)) {
                $data = fread($apns, 38);
                if (strlen($data)) {
                    $feedback_tokens['apnsFeedback'][] = unpack("N1timestamp/n1length/H*devtoken", $data);
                }
            }
            fclose($apns);

        } catch (\Exception $e) {
            //if stream socket can't be established, try again
            if ($this->canRetry()) {
                return $this->apnsFeedback();
            }

            $response = [
                'success' => false,
                'error' => 'APNS feedback connection problem: ' . $e->getMessage() . PHP_EOL
            ];

            $this->setFeedback(json_decode(json_encode($response)));

        } finally {
            $this->resetAttempts();
            return $feedback_tokens;
        }
    }

    /**
     * Update the success and failure values based on apple feedback
     *
     * @param $apnsFeedback
     * @param $feedback
     * @param $deviceTokens
     *
     * @return array $feedback
     */
    private function updateCustomFeedbackValues($apnsFeedback, $feedback, $deviceTokens)
    {

        //Add failures amount based on apple feedback to our custom feedback
        $feedback['failure'] += count($apnsFeedback['apnsFeedback']);

        //apns tokens
        $apnsTokens = Arr::pluck($apnsFeedback['apnsFeedback'], 'devtoken');

        foreach ($deviceTokens as $token) {
            if (in_array($token, $apnsTokens)) {
                $feedback['success'] -= 1;
                $feedback['tokenFailList'][] = $token;
            }

        }

        return $feedback;
    }

}