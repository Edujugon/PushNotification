<?php


use Edujugon\PushNotification\PushNotification;

class PushNotificationTest extends PHPUnit_Framework_TestCase {

    public function testSend()
    {
        $push = new PushNotification;
        $push->setMessage(['message'=>'This is the message'])
                ->setApiKey('AIzaSyAtbf2RXXoYyQx2SJbumNr_hUS6Rkrv3W8')
                ->setDevicesToken(['d1WXouhHG34:APA91bF2byCOq-gexmHFqysYX_UCV4_ro53UKhkzqKnVryuNyYMd-wRMkbIlfTEM2Wjhwgct3wC4XsflVpgfR72XRvIEV3kIcD7GGSHZ81hnNz8zJVaoH_PaYK8EIW2uvQt4KkAVDQJX']);
        $push->send();
        var_dump($push->service->feedback);
        $this->assertTrue($push->send());
    }

    public function testSetAndGetConfig(){

        $push = new PushNotification();

        $push->service->setConfig(['priority'=>'normal']);
    }
}