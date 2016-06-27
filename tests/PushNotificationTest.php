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
        $this->push->setMessage(['message'=>'Hello World'])
                ->setApiKey('XXofYyQx2SJbumNrs_hUS6Rkrv3W8asd')
                ->setDevicesToken(['d1WaXouhHG34:AaPA91bF2byCOq-gexmHFqdysYX'])
                ->setConfig(['dry_run' => true]);
        
        $this->assertInstanceOf('stdClass',$this->push->send());

        $this->assertTrue(isset($this->push->feedback->error));
    }

    public function test_push_response()
    {
        $this->push->setApiKey('AIzaSyBIs2KtnE2cSaFvKaB8pWO-zpNxSGF2sg0')
            ->setDevicesToken([
                'eXoyga5obuM:APA91bGc1Nf_968qAmIzrrK48zJn2U2Kpos_0WuhabpZO9B_rFveB635X7Ksv7x6XCXQ9cvvpLMuxkaJ0ySpWPed3cvz0q4fuG1SXt40-oXH4R5dwYk4rQYTeds3nhWE5OKDmatFZaaZ',
                'asfasdfasdf_968qAmIzrrK48zJn2U2Kpos_0WuhabpZO9B_rFveB635X7Ksv7x6XCXQ9cvvpLMuxkaJ0ySpWPed3cvz0q4fuG1SXt40-oXH4R5dwYk4rQYTeds3nhWE5OKDmatFZaaZ'
            ])
            ->setConfig(['dry_run' => true])
            ->setMessage('hello world')
            ->send();

        //var_dump($this->push->service->feedback);

    }

    public function test_set_and_get_service_config(){


        $this->push->setConfig(['time_to_live' => 3]);

        $this->assertArrayHasKey('time_to_live',$this->push->config);
    }

    public function test_set_message_data(){
        
        $this->push->setMessage('This is the message');

        $this->assertArrayHasKey('message',$this->push->message);
        
        //var_dump($this->push->message);
    }

    public function test_config_usage(){

        $this->assertInternalType('array',$this->push->config);
    }
}