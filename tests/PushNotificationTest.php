<?php


use Edujugon\PushNotification\PushNotification;

class PushNotificationTest extends PHPUnit_Framework_TestCase {

    /** @test */
    public function push_notification_instance_creation_without_argument_set_gcm_as_service()
    {
        $push = new PushNotification();

        $this->assertInstanceOf('Edujugon\PushNotification\Gcm',$push->service);
    }

    /** @test */
    public function assert_send_method_returns_an_stdClass_instance()
    {
        $push = new PushNotification();

        $push->setMessage(['message'=>'Hello World'])
                ->setApiKey('AIzaSyAjsu5asdf4N9KyCxCB04')
                ->setDevicesToken(['howoPaqCPp1pvVsBZ6QUHoEtO_S9-Esel4N7nqeUypQ6ah8MKZKo6jl'])
                ->setConfig(['dry_run' => true]);

        $push = $push->send();

        $this->assertInstanceOf('stdClass',$push->getFeedback());

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
        $pushAPN = new PushNotification('apn');

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
        $push = new PushNotification('apn');

        $message = [
            'aps' => [
                'alert' => [
                    'title' => '1 Notification test',
                    'body' => 'Just for testing purposes'
                ],
                'sound' => 'default'

            ]
        ];

        $push->setMessage($message)
            ->setDevicesToken([
                '507e3adaf433ae3e6234f35c82f8a43ad0d84218bff08f16ea7be0869f066c0312',
                'ac566b885e91ee74a8d12482ae4e1dfd2da1e26881105dec262fcbe0e082a358',
                '507e3adaf433ae3e6234f35c82f8a43ad0d84218bff08f16ea7be0869f066c0312'
            ]);

        $push = $push->send();
        //var_dump($push->getFeedback());
        $this->assertInstanceOf('stdClass',$push->getFeedback());
        $this->assertInternalType('array',$push->getUnregisteredDeviceTokens());
    }

    /** @test */
    public function apn_without_certificate()
    {
        $push = new PushNotification('apn');

        $push->setConfig(['custom' => 'Custom Value','certificate' => 'MycustomValue']);
        $push->send();
        $this->assertTrue(isset($push->feedback->error));
        $this->assertFalse($push->feedback->success);
    }

    /** @test */
    public function apn_dry_run_option_update_the_apn_url()
    {
        $push = new PushNotification('apn');

        $push->setConfig(['dry_run'=>false]);

        $this->assertEquals('ssl://gateway.push.apple.com:2195',$push->url);

        $push->setConfig(['dry_run'=>true]);

        $this->assertEquals('ssl://gateway.sandbox.push.apple.com:2195',$push->url);
    }


    /** @test */
    public function fcm_assert_send_method_returns_an_stdClass_instance()
    {
        $push = new PushNotification('fcm');

        $push->setMessage(['message'=>'Hello World'])
            ->setApiKey('asdfasdffasdfasdfasdf')
            ->setDevicesToken(['asdfasefaefwefwerwerwer'])
            ->setConfig(['dry_run' => false]);

        $push = $push->send();

        $this->assertEquals('https://fcm.googleapis.com/fcm/send',$push->url);
        $this->assertInstanceOf('stdClass',$push->getFeedback());

    }

    /** @test */
    public function if_push_service_as_argument_is_not_valid_user_gcm_as_default()
    {
        $push = new PushNotification('asdf');

        $this->assertInstanceOf('Edujugon\PushNotification\Gcm',$push->service);


    }
    /** @test */
    public function get_available_push_service_list()
    {
        $push = new PushNotification();

        $this->assertCount(3,$push->servicesList);
        $this->assertInternalType('array',$push->servicesList);
    }

    /** @test */
    public function if_argument_in_set_service_method_does_not_exist_set_the_service_by_default(){
        $push = new PushNotification();

        $push->setService('asdf')->send();
        $this->assertInstanceOf('Edujugon\PushNotification\Gcm',$push->service);

        $push->setService('fcm');
        $this->assertInstanceOf('Edujugon\PushNotification\Fcm',$push->service);
    }

    /** @test */
    public function get_feedback_after_sending_a_notification()
    {
        $push = new PushNotification('fcm');

        $response = $push->setMessage(['message'=>'Hello World'])
            ->setApiKey('asdfasdffasdfasdfasdf')
            ->setDevicesToken(['asdfasefaefwefwerwerwer'])
            ->setConfig(['dry_run' => false])
            ->send()
            ->getFeedback();

        $this->assertInstanceOf('stdClass',$response);
    }

    /** @test */
    public function apn_feedback()
    {

        $push = new PushNotification('apn');
        $message = [
            'aps' => [
                'alert' => [
                    'title' => 'New Notification test',
                    'body' => 'Just for testing purposes'
                ],
                'sound' => 'default'

            ]
        ];

        $push->setMessage($message)
            ->setDevicesToken([
                'asdfasdf'
            ]);

        $push->send();
        $this->assertInstanceOf('stdClass',$push->getFeedback());
        $this->assertInternalType('array',$push->getUnregisteredDeviceTokens());

    }


    /** @test */
    public function allow_apikey_from_config_file()
    {
        $push = new PushNotification();

        $response = $push->setMessage(['message'=>'Hello World'])
            ->setDevicesToken(['asdfasefaefwefwerwerwer'])
            ->setConfig(['dry_run' => true])
            ->send()
            ->getFeedback();

        $this->assertInstanceOf('stdClass',$response);

    }

    /** @test */
    public function fake_unregisteredDevicesToken_with_apn_feedback_response_merged_to_our_custom_feedback()
    {

        $primary = [
            'success' => 3,
            'failure' => 1,
            'tokenFailList' => ['asdf']
        ];
        $array =[
            'apnsFeedback' => [
                [
                'timestamp' => 121212,
                'length' => 23,
                'devtoken' => '2121221212'
                ],
                [
                    'timestamp' => 5454545,
                    'length' => 32,
                    'devtoken' => '34343434'

                ]
            ]
        ];
        $merge = array_merge($primary,$array);
        $obj = json_decode(json_encode($merge), FALSE);

        $tokens = [];

        if(! empty($obj->tokenFailList))
            $tokens =  $obj->tokenFailList;
        if(!empty($obj->apnsFeedback))
            $tokens = array_merge($tokens,array_pluck($obj->apnsFeedback,'devtoken'));

        //var_dump($tokens);
    }

    /** @test */
    public function send_a_notification_by_topic_in_fcm()
    {
        $push = new PushNotification('fcm');

        $response = $push->setMessage(['message'=>'Hello World'])
            ->setApiKey('asdfasdffasdfasdfasdf')
            ->setConfig(['dry_run' => false])
            ->sendByTopic('test')
            ->getFeedback();

        $this->assertInstanceOf('stdClass',$response);
    }

    /** @test */
    public function send_a_notification_by_condition_in_fcm()
    {
        $push = new PushNotification('fcm');

        $response = $push->setMessage(['message'=>'Hello World'])
            ->setApiKey('asdfasdffasdfasdfasdf')
            ->setConfig(['dry_run' => false])
            ->sendByTopic("'dogs' in topics || 'cats' in topics",true)
            ->getFeedback();

        $this->assertInstanceOf('stdClass',$response);
    }

    public function apn_connection_attempts_default() {
        $push = new PushNotification('apn');

        $push->setConfig(['dry_run' => true]);

        $key = 'connection_attempts';
        $this->assertArrayNotHasKey($key, $push->config);
    }

    /** @test */
    public function set_apn_connect_attempts_override_default() {
        $push = new PushNotification('apn');

        $expected = 0;
        $push->setConfig([
            'dry_run' => true,
            'connection_attempts' => $expected,
        ]);

        $key = 'connection_attempts';
        $this->assertArrayHasKey($key, $push->config);
        $this->assertEquals($expected, $push->config[$key]);
    }

    /** @test */
    public function apn_connect_attempts_bailout_badcert() {
        $push = new PushNotification('apn');

        $tmp_name = tempnam(sys_get_temp_dir(), 'apn-tmp');
        $fh = fopen($tmp_name, 'w');
        fwrite($fh, 'badcert');
        fclose($fh);

        $expected = 0;

        // ZZZ: intentional failure use-case so let's not
        // waste time attemping to push with a bad cert.
        $push->setConfig([
            'dry_run' => true,
            'connection_attempts' => 1,
            'certificate' => $tmp_name,
        ]);

        $message = [
            'aps' => [
                'alert' => [
                    'title' => '1 Notification test',
                    'body' => 'Just for testing purposes'
                ],
                'sound' => 'default'
            ]
        ];

        $push->setMessage($message)
            ->setDevicesToken(['507e3adaf433ae3e6234f35c82f8a43ad0d84218bff08f16ea7be0869f066c0312']);

        $push = $push->send();
        $this->assertInstanceOf('stdClass', $push->getFeedback());

        unlink($tmp_name);
    }

}
