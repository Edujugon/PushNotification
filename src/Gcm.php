<?php
namespace Edujugon\PushNotification;

use Edujugon\PushNotification\Contracts\PushServiceInterface;

class Gcm extends PushService implements PushServiceInterface
{
    
    /**
     * Gcm constructor.
     * 
     */
    public function __construct()
    {
        $this->url = 'https://android.googleapis.com/gcm/send';
        
        $this->config = $this->initializeConfig('gcm');
    }

    /**
     *Provide the unregistered tokens of the notification sent.
     *
     * @return array $tokenUnRegistered
     */
    public function getUnregisteredDeviceTokens($devices_token)
    {
        /**
         * If there is any failure sending the notification
         */
        if($this->feedback && $this->feedback->failure)
        {

            $unRegisteredTokens = $devices_token;

            /**
             * Walk the array looking for any error.
             * If no error, unset it from all token list which will become the unregistered tokens array.
             */
            foreach ($this->feedback->results as $key => $message)
            {
                if(! isset($message->error)) unset( $unRegisteredTokens[$key] );
            }

            return $unRegisteredTokens;
        }

        return [];
    }

    private function addRequestFields($deviceTokens,$message){
        return [
            'registration_ids'  => $deviceTokens,
            'data'     => $message,
            'dry_run' => $this->config['dry_run']
        ];
    }

    private function addRequestHeaders(){
        return [
            'Authorization' => 'key=' . $this->api_key,
            'Content-Type:' =>'application/json'
        ];
    }

    /**
     * Send Push Notification
     * @param \GuzzleHttp\Client client
     * @param  array $deviceTokens
     * @param array $message
     * @return JSON  GCM Response
     */
    public function send($client,array $deviceTokens,array $message){
        
        $fields = $this->addRequestFields($deviceTokens,$message);

        $headers = $this->addRequestHeaders();

        try
        {
            $result = $client->post(
                $this->url,
                [
                    'headers' => $headers,
                    'json' => $fields,
                ]
            );

            $json = $result->getBody();

            $this->setFeedback(json_decode($json));

        }catch (\Exception $e)
        {
            $response = ['success' => false, 'error' => $e->getMessage()];
            
            $this->setFeedback(json_decode(json_encode($response), FALSE));

        }finally
        {
            return $this->feedback;
        }

    }
}