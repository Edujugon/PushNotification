<?php


use Edujugon\PushNotification\PushNotification;

class PushNotificationTest extends PHPUnit_Framework_TestCase {

    /** @test */
    public function assert_send_method_returns_an_stdClass_instance()
    {
        $push = new PushNotification();

        $push->setMessage(['message'=>'Hello World'])
                ->setApiKey('XXofYyQx2SJbumNrs_hUS6Rkrv3W8asd')
                ->setDevicesToken(['d1WaXouhHG34:AaPA91bF2byCOq-gexmHFqdysYX'])
                ->setConfig(['dry_run' => true]);
        
        $this->assertInstanceOf('stdClass',$push->send());

    }
    /** @test */
    public function assert_there_is_an_array_key_called_error()
    {
        $push = new PushNotification();

        $push->setMessage(['message'=>'Hello World'])
            ->setApiKey('XXofYyQx2SJbumNrs_hUS6Rkrv3W8asd')
            ->setDevicesToken(['d1WaXouhHG34:AaPA91bF2byCOq-gexmHFqdysYX'])
            ->setConfig(['dry_run' => true])
            ->send();

        $this->assertTrue(isset($push->feedback->error));
    }

    /** @test */
    public function assert_unregistered_device_tokens_is_an_array()
    {
        $push = new PushNotification();

        $push->setApiKey('wefwef23f23fwef')
            ->setDevicesToken([
                'asdfasdfasdfasdfXCXQ9cvvpLMuxkaJ0ySpWPed3cvz0q4fuG1SXt40-oasdf3nhWE5OKDmatFZaaZ',
                'asfasdfasdf_96ssdfsWuhabpZO9Basvz0q4fuG1SXt40-oXH4R5dwYk4rQYTeds3nhWE5OKDmatFZaaZ'
            ])
            ->setConfig(['dry_run' => true])
            ->setMessage(['message' =>'hello world'])
            ->send();

        $this->assertInternalType('array',$push->getUnregisteredDeviceTokens());

    }

    /** @test */
    public function set_and_get_service_config(){

        /** GCM */
        $push = new PushNotification();

        $push->setConfig(['time_to_live' => 3]);

        $this->assertArrayHasKey('time_to_live',$push->config);
        $this->assertArrayHasKey('priority',$push->config); //default key
        $this->assertInternalType('array',$push->config);

        /** APNS */
        $pushAPN = new PushNotification(new \Edujugon\PushNotification\Apn());

        $pushAPN->setConfig(['time_to_live' => 3]);

        $this->assertArrayHasKey('time_to_live',$pushAPN->config);
        $this->assertArrayHasKey('certificate',$pushAPN->config); //default key
        $this->assertInternalType('array',$pushAPN->config);
    }

    /** @test */
    public function set_message_data(){

        $push = new PushNotification();

        $push->setMessage(['message' =>'hello world']);

        $this->assertArrayHasKey('message',$push->message);

        $this->assertEquals('hello world',$push->message['message']);

    }

    /** @test */
    public function send_method_in_apn_service(){
        $push = new PushNotification(new \Edujugon\PushNotification\Apn());

        $message = [
            'aps' => [
                'alert' => [
                    'title' => 'This is the title',
                    'body' => 'This is the body'
                ],
                'sound' => 'default'

            ],
            'data' => [
                'text' => 'This the text',
                'client_url' => 'http://bing.com',
            ]
        ];

        $push->setMessage($message)
            ->setDevicesToken([
                '123123aasfasfd',
                'asdfwef'
            ]);
        $this->assertInstanceOf('stdClass',$push->send());
        $this->assertCount(2,$push->getUnregisteredDeviceTokens());
        $this->assertInternalType('array',$push->getUnregisteredDeviceTokens());
    }

    /** @test */
    public function apn_without_certificate()
    {
        $push = new PushNotification(new \Edujugon\PushNotification\Apn());

        $push->setConfig(['custom' => 'Custom Value','certificate' => 'MycustomValue']);
        $push->send();
        $this->assertTrue(isset($push->feedback->error));
        $this->assertFalse($push->feedback->success);
    }
}