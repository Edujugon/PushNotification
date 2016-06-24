# PushNotification Package

This is is a light and easy to use package to send push notification.

## Usage

Instance the Class

    $push = new PushNotification;

By default it will use:

*   GuzzleHttp\Client as Http client.
*   GCM as Push Service provider.

### Push Service configuration

By default Gcm is been used.

The default configuration fields are:

*   priority => normal
*   dry_run => false

You can easily change those values or adding new ones calling the method setConfig like so:

    $push->setConfig(
        'priority' => 'high',
        'dry_run' => true,
        'time_to_live' => 3
    );

### Filling the Notification options

You can chain the methods

    $push->setMessage(['message'=>'This is the message','title'=>'This is the title'])
                    ->setApiKey('Server-API-Key')
                    ->setDevicesToken(['deviceToken1','deviceToken2','deviceToken3'...]);

or do it separately

    $push->setMessage([
        'message'=>'This is the message',
        'title'=>'This is the title'
    ]);
    $push->setApiKey('Server-API-Key');
    $push->setDevicesToken(['deviceToken1'
        ,'deviceToken2',
        'deviceToken3'
    ]);

If you want send the notification to only 1 device, you may pass the value as string.

    $push->setDevicesToken('deviceToken');

Sending only a message field?

    $push->setMessage('My message here..');

### Send the Notification

Method send return true or false.

    $push->send();

### Review the Notification Response

If you want to see the push service response

    $push->feedback;

or

    $push->service->feedback;

