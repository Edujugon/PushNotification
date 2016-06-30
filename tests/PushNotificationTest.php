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

    public function test_get_unregistered_device_tokens()
    {
        $this->push->setApiKey('AIzaSyBIs2KtnE2cSaFvKaB8pWO-zpNxSGF2sg0')
            ->setDevicesToken([
                'eXoyga5obuM:APA91bGc1Nf_968qAmIzrrK48zJn2U2Kpos_0WuhabpZO9B_rFveB635X7Ksv7x6XCXQ9cvvpLMuxkaJ0ySpWPed3cvz0q4fuG1SXt40-oXH4R5dwYk4rQYTeds3nhWE5OKDmatFZaaZ',
                'asfasdfasdf_968qAmIzrrK48zJn2U2Kpos_0WuhabpZO9B_rFveB635X7Ksv7x6XCXQ9cvvpLMuxkaJ0ySpWPed3cvz0q4fuG1SXt40-oXH4R5dwYk4rQYTeds3nhWE5OKDmatFZaaZ'
            ])
            ->setConfig(['dry_run' => true])
            ->setMessage(['message' =>'hello world'])
            ->send();

        $this->assertCount(1,$this->push->getUnregisteredDeviceTokens());

    }

    public function test_set_and_get_service_config(){


        $this->push->setConfig(['time_to_live' => 3]);

        $this->assertArrayHasKey('time_to_live',$this->push->config);
    }

    public function test_set_message_data(){
        
        $this->push->setMessage(['message' =>'hello world']);

        $this->assertArrayHasKey('message',$this->push->message);
        
        //var_dump($this->push->message);
    }

    public function test_config_usage(){

        $this->assertInternalType('array',$this->push->config);
    }

    public function test_apn_service(){
        $this->push = new PushNotification(new \Edujugon\PushNotification\Apn());

        $message = [
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

        $this->push->setMessage($message)
            ->setDevicesToken([
                '91d70094420a01072a520a621990c276f179471a57e64d0cef5bdda4a9bc22e8',
                '123123aasfasfd',
                'asdfwef'
            ]);
        $this->assertInstanceOf('stdClass',$this->push->send());
        $this->assertCount(2,$this->push->getUnregisteredDeviceTokens());

        //test getUnregisteredDevices without errors
        $this->push->setDevicesToken([
                '91d70094420a01072a520a621990c276f179471a57e64d0cef5bdda4a9bc22e8'
            ])->send();
        $this->assertInstanceOf('stdClass',$this->push->send());
        $this->assertInternalType('array',$this->push->getUnregisteredDeviceTokens());
        $this->assertCount(0,$this->push->getUnregisteredDeviceTokens());
    }

    public function test_apn_config_set_and_get()
    {
        $this->push = new PushNotification(new \Edujugon\PushNotification\Apn());
        $this->push->setConfig(['custom' => 'Custom Value']);

        $this->assertArrayHasKey('custom',$this->push->config);
    }

    public function test_apn_no_certificate()
    {
        $this->push = new PushNotification(new \Edujugon\PushNotification\Apn());
        $this->push->setConfig(['custom' => 'Custom Value','certificate' => 'MycustomValue']);
        $this->push->send();
        $this->assertTrue(isset($this->push->feedback->error));
    }
}