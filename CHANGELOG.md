#   ChangeLog

##  v2.0

Now you can create the object with the push service provider only typing the name

    $push = new PushNotification('apn');

Addition of a new Push service provider : FCM

    $push = new PushNotification('fcm');

As previous versions, if you instance the class without argument. it will set GCM as default Push service provider

    $push = new PushNotification();


##  v1.0

Usage

    $push = new PushNotification;

or for APN

    $push = new PushNotification(new \Edujugon\PushNotification\Apn());
