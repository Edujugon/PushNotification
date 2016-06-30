# PushNotification Package

This is a lightly and easy to use package to send push notification.

## Instalation

Update your composer.json file.

    "edujugon/push-notification": "dev-master"

Then

    composer update

## Laravel 5.*

Register the PushNotification service by adding it to the providers array.

    'providers' => array(
        ...
        Edujugon\PushNotification\Providers\PushNotificationServiceProvider::class
    )

Let's add the Alias facade, add it to the aliases array.

    'aliases' => array(
        ...
        'PushNotification' => Edujugon\PushNotification\Facades\PushNotification::class,
    )

Publish the package's configuration file to the application's own config directory

    php artisan vendor:publish --provider="Edujugon\PushNotification\Providers\PushNotificationServiceProvider" --tag="config"

## Usage

    $push = new PushNotification;

By default it will use GCM as Push Service provider.

If you want to use APNS:

    $push = new PushNotification(new \Edujugon\PushNotification\Apn());

### Push Service configuration

The default configuration for all Push service providers is located in Config/config.php

The default configuration parameters for GCM are :

*   priority => normal
*   dry_run => false

You can dynamically update those values or adding new ones calling the method setConfig like so:

    $push->setConfig(
        'priority' => 'high',
        'dry_run' => true,
        'time_to_live' => 3
    );


The default configuration parameters for APNS are:

*   certificate => __DIR__ . '/iosCertificates/yourCertificate.pem'
*   passPhrase => 'MyPassPhrase'

Also you can update those values and add more dynamically

    $this->push->setConfig(['passPhrase' => 'NewPass','custom' => 'MycustomValue']);


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


APNS message could be like so:

    $this->push->setMessage([
                'aps' => [
                    'alert' => [
                        'title' => 'This is the title',
                        'body' => 'This is the body'
                    ],
                    'sound' => 'default'

                ],
                'extraPayLoad' => [
                    'custom' => 'My custom data',
                ]
            ]);

### Send the Notification

Method send() returns Push service Respose as an Object (stdClass).

    $push->send();

### Review the Notification Response

If you want to retrieve the push service response again, then:

    $push->feedback;

or

    $push->service->feedback;


### Check if there was any error sending the push notification

    if(isset($push->feedback->error)){
        ....
    }

### Get Unregistered Devices tokens

After sending a notification, you may retrieve the list of unregistered tokens

    $push->getUnregisteredDeviceTokens();

This method returns an array of unregistered tokens from the Push service provider. If there aren't any unregistered tokens, returns an empty array.

