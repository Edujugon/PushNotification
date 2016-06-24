<?php


use Edujugon\PushNotification\PushNotification;

class PushNotificationTest extends PHPUnit_Framework_TestCase {

    public function test_send_notication()
    {

        $push = new PushNotification;

        $push->setMessage(['message'=>'This is the message'])
                ->setApiKey('AfIzaSyaAtbf2RXXofYyQx2SJbumNrs_hUS6Rkrv3W8asd')
                ->setDevicesToken(['d1WaXouhHG34:AaPA91bF2byCOq-gexmHFqdysYXf_UCV4_ro53UqKnVryuNyYMd-wRMkbIlfTEM2Wjhwgcft3wC4XsflVpgfR72XRvIEaV3aGGSHZ81hnNz8zJVaoH_PaYK8EIW2uvQt4KkAVDQJX']);

        $this->assertFalse($push->send());

        //var_dump($push->service->feedback);
    }

    public function test_set_and_get_service_config(){

        $push = new PushNotification();

        $push->service->setConfig(['priority'=>'normal']);

        $this->assertNotEmpty($push->service->config);
    }
}