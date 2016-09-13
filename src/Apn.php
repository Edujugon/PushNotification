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
    //private $sandboxUrl = 'ssl://gateway.sandbox.push.apple.com:2195';

    private $sandboxUrl = 'ssl://feedback.sandbox.push.apple.com:2196';

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

        $this->setProperGateway();
    }

    /**
     * Call parent method.
     * Check if there is dry_run parameter in config data. Set the service url according to the dry_run value.
     *
     * @param array $config
     * @return mixed|void
     */
    public function setConfig(array $config)
    {
        parent::setConfig($config);

        $this->setProperGateway();

    }

    /**
     *Set the correct Gateway url based on dry_run param
     */
    private function setProperGateway()
    {
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

            usleep(500000);

            if(feof($fp))
            {
                $fp = $this->openConnectionAPNS();

                $feedback['tokenFailList'][] = $token;
                $feedback['failure'] += 1;
            }else
                $feedback['success'] += 1;

            $this->checkAppleErrorResponse($fp);

            //var_dump($result);

//            if (!$result)
//            {
//                $feedback['tokenFailList'][] = $token;
//                $feedback['failure'] += 1;
//
//            }else
//                $feedback['success'] += 1;

        }

        $this->setFeedback(json_decode(json_encode($feedback), FALSE));

        // Close the connection to the server
        fclose($fp);

        return $this->feedback;

    }

    //FUNCTION to check if there is an error response from Apple
//         Returns TRUE if there was and FALSE if there was not
    private function checkAppleErrorResponse($fp) {

        //byte1=always 8, byte2=StatusCode, bytes3,4,5,6=identifier(rowID). Should return nothing if OK.
        $apple_error_response = fread($fp, 6);
        //NOTE: Make sure you set stream_set_blocking($fp, 0) or else fread will pause your script and wait forever when there is no response to be sent.

        if ($apple_error_response) {
            //unpack the error response (first byte 'command" should always be 8)
            $error_response = unpack('Ccommand/Cstatus_code/Nidentifier', $apple_error_response);

            if ($error_response['status_code'] == '0') {
                $error_response['status_code'] = '0-No errors encountered';
            } else if ($error_response['status_code'] == '1') {
                $error_response['status_code'] = '1-Processing error';
            } else if ($error_response['status_code'] == '2') {
                $error_response['status_code'] = '2-Missing device token';
            } else if ($error_response['status_code'] == '3') {
                $error_response['status_code'] = '3-Missing topic';
            } else if ($error_response['status_code'] == '4') {
                $error_response['status_code'] = '4-Missing payload';
            } else if ($error_response['status_code'] == '5') {
                $error_response['status_code'] = '5-Invalid token size';
            } else if ($error_response['status_code'] == '6') {
                $error_response['status_code'] = '6-Invalid topic size';
            } else if ($error_response['status_code'] == '7') {
                $error_response['status_code'] = '7-Invalid payload size';
            } else if ($error_response['status_code'] == '8') {
                $error_response['status_code'] = '8-Invalid token';
            } else if ($error_response['status_code'] == '255') {
                $error_response['status_code'] = '255-None (unknown)';
            } else {
                $error_response['status_code'] = $error_response['status_code'] . '-Not listed';
            }

            var_dump('Response Command: ' . $error_response['command'] . ' Identifier: ' . $error_response['identifier'] . ' Status: ' . $error_response['status_code']);


            return true;
        }
        return false;
    }

}