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