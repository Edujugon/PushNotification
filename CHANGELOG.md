#   ChangeLog

##  v2.1
Laravel Facade Usage integrated into the package.

Now if you wanna get the Push Feedback after sending a notification you can chain the method getFeedback after send method.


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
