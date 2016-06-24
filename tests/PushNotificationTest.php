<?php


use Edujugon\PushNotification\PushNotification;

class PushNotificationTest extends PHPUnit_Framework_TestCase {

    protected $push;

    /**
     * PushNotificationTest constructor.
     * @param PushNotification $push
     */
    public function __construct()
    {
        $this->push = new PushNotification();
    }

    public function test_send_notication()
    {

        $this->push->setMessage(['message'=>'This is the message'])
                ->setApiKey('XXofYyQx2SJbumNrs_hUS6Rkrv3W8asd')
                ->setDevicesToken(['d1WaXouhHG34:AaPA91bF2byCOq-gexmHFqdysYX']);
        
        $this->assertFalse($this->push->send());

        //var_dump($this->push->feedback);
    }

    public function test_set_and_get_service_config(){


        $this->push->setConfig(['priority'=>'normal']);
        //var_dump($this->push->message);
        $this->assertNotEmpty($this->push->config);
    }

    public function test_set_message_data(){
        
        $this->push->setMessage('This is the message');

        $this->assertArrayHasKey('message',$this->push->message);
        
        //var_dump($this->push->message);
    }
}