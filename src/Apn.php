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
     *  It's automatically filled based on the dry_run parameter.
     *
     * @var string
     */
    private $feedbackUrl;

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

    }

    /**
     *Set the correct Gateway url and the Feedback url based on dry_run param.
     *
     * @return void
     */
    private function setProperGateway()
    {
        if(isset($this->config['dry_run']))
        {
            if($this->config['dry_run']){
                $this->setUrl($this->sandboxUrl);
                $this->feedback = $this->feedbackSandboxUrl;

            }else {
                $this->setUrl($this->productionUrl);
                $this->feedback = $this->feedbackProductionUrl;
            }
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
     * Set the feedback with no exist any certificate.
     *
     * @return mixed|void
     */
    private function messageNoExistCertificate()
    {
        $response = ['success' => false, 'error' => "Please, add your APN certificate to the iosCertificates folder." . PHP_EOL];

        $this->setFeedback(json_decode(json_encode($response), FALSE));
    }

    /**
     * Check if the certificate file exist.
     * @return bool
     */
    private function existCertificate()
    {
        if(isset($this->config['certificate']))
        {
            $certificate = $this->config['certificate'];
            if(!file_exists($certificate))
            {
                $this->messageNoExistCertificate();
                return false;
            }

            return true;
        }

        $this->messageNoExistCertificate();
        return false;
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

        $ctx = stream_context_create();

        //Already checked if certificate exists.
        $certificate = $this->config['certificate'];
        stream_context_set_option($ctx, 'ssl', 'local_cert', $certificate);

        if(isset($this->config['passPhrase']))
        {
            $passPhrase = $this->config['passPhrase'];
            if(!empty($passPhrase)) stream_context_set_option($ctx, 'ssl', 'passphrase', $passPhrase);
        }

        if(isset($this->config['passFile']))
        {
            $passFile = $this->config['passFile'];
            if(file_exists($passFile)) stream_context_set_option($ctx, 'ssl', 'local_pk', $passFile);
        }

        // Open a connection to the APNS server
        $fp = stream_socket_client(
            $this->url, $err,
            $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

        stream_set_blocking ($fp, 0);

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
     * @return \stdClass  APN Response
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

        //var_dump($this->send_feedback_request($this->config['certificate'],$this->feedbackSandboxUrl));

        return $this->feedback;

    }

    /**
     * Get the unregistered device tokens from the apns list.
     * Connect to apn server in order to collect the tokens of the apps which were removed from the device.
     *
     * @return object
     */
    public function apnsFeedback() {


        if(!$this->existCertificate()) return $this->feedback;
        $certificate = $this->config['certificate'];

        //connect to the APNS feedback servers
        $stream_context = stream_context_create();
        stream_context_set_option($stream_context, 'ssl', 'local_cert', $certificate);
        $apns = stream_socket_client($this->feedbackUrl, $errcode, $errstr, 60, STREAM_CLIENT_CONNECT, $stream_context);
        if(!$apns) {
            echo "ERROR $errcode: $errstr\n";
            return;
        }


        $feedback_tokens = array();
        //and read the data on the connection:
        while(!feof($apns)) {
            $data = fread($apns, 38);
            if(strlen($data)) {
                $feedback_tokens[] = unpack("N1timestamp/n1length/H*devtoken", $data);
            }
        }
        fclose($apns);
        return $feedback_tokens;
    }

}