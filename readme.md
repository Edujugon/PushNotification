# PushNotification Package

This is a lightly and easy to use package to send push notification.

####Push Service Providers Available:

* GCM
* FCM
* APN
* More Push Service Providers coming soon.

## Installation

type in console:

        composer require edujugon/push-notification


Or update your composer.json file.

    "edujugon/push-notification": "2.1.*"

Then

    composer install

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

> Go to [laravel facade sample](https://github.com/edujugon/PushNotification#laravel-alias-facade) directly.

### Configuration

The default configuration for all Push service providers is located in Config/config.php

The default configuration parameters for **GCM** and **FCM** are :

*   priority => normal
*   dry_run => false

You can dynamically update those values or adding new ones calling the method setConfig like so:

    $push->setConfig([
        'priority' => 'high',
        'dry_run' => true,
        'time_to_live' => 3
    ]);


The default configuration parameters for **APN** are:

*   certificate => __DIR__ . '/iosCertificates/yourCertificate.pem'
*   passPhrase => 'MyPassPhrase' or passFile => __DIR__ . '/iosCertificates/yourKey.pem'
*   dry_run => false

Also you can update those values and add more dynamically

    $push->setConfig([
        'passPhrase' => 'NewPass',
        'custom' => 'MycustomValue',
        'dry_run' => true
    ]);

Even you may update the url of the Push Service dynamically like follows:

    $puhs->setUrl('http://newPushServiceUrl.com');

> Not update the url unless it's really necessary.

## Usage

    $push = new PushNotification;

By default it will use GCM as Push Service provider.

For APN Service:

    $push = new PushNotification('apn');

For FCM Service:

    $push = new PushNotification('fcm');
    
Now you may use any method what you need. Please see the API List.


## API List

- [setMessage](https://github.com/edujugon/PushNotification#setmessage)
- [setApiKey](https://github.com/edujugon/PushNotification#setapikey)
- [setDevicesToken](https://github.com/edujugon/PushNotification#setdevicestoken)
- [send](https://github.com/edujugon/PushNotification#send)
- [getFeedback](https://github.com/edujugon/PushNotification#getfeedback)
- [getUnregisteredDeviceTokens](https://github.com/edujugon/PushNotification#getunregistereddevicetokens)
- [setConfig](https://github.com/edujugon/PushNotification#setconfig)
- [setUrl](https://github.com/edujugon/PushNotification#seturl)

> Or go to [Usage samples](https://github.com/edujugon/PushNotification#usage-samples) directly.

#### setMessage

`setMessage` method sets the message parameters, which you pass the name through parameter as array.

**Syntax**

```php
object setMessage(array $data)
```

#### setApiKey

`setApiKey` method sets the API Key of your App, which you pass the name through parameter as string.

**Syntax**

```php
object setApiKey($api_key)
```

#### setDevicesToken

`setDevicesToken` method sets the devices' tokens, which you pass the name through parameter as array or string if it was only one.

**Syntax**

```php
object setDevicesToken($deviceTokens)
```

#### send

`send` method sends the notification.

**Syntax**

```php
object send()
```

#### getFeedback

`getFeedback` method gets the notification response, which you may use it chaining it to `send` method or call it whenever after sending a notification.

**Syntax**

```php
object getFeedback()
```

#### getUnregisteredDeviceTokens

`getUnregisteredDeviceTokens` method gets the devices' tokens that couldn't receive the notification because they aren't registered to the Push service provider. 
You may use it chaining it to `send` method or call it whenever after sending a notification.

**Syntax**

```php
array getUnregisteredDeviceTokens()
```

#### setConfig

`setConfig` method sets the Push service configuration, which you pass the name through parameter as an array.

**Syntax**

```php
object setConfig(array $config)
```

#### setUrl

`setUrl` method sets the Push service url, which you pass the name through parameter as a string.

**Syntax**

```php
object setUrl($url)
```
> Not update the url unless it's really necessary.

### Usage samples

>You can chain the methods.

GCM sample:

    $push->setMessage(['message'=>'This is the message','title'=>'This is the title'])
                    ->setApiKey('Server-API-Key')
                    ->setDevicesToken(['deviceToken1','deviceToken2','deviceToken3'...]);

APN sample:

    $push->setMessage([
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
            ])
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

### Send the Notification

Method send() can be also chained to the above methods.

    $push->setMessage(['message'=>'This is the message','title'=>'This is the title'])
                        ->setApiKey('Server-API-Key')
                        ->setDevicesToken(['deviceToken1','deviceToken2','deviceToken3'...])
                        ->send();

### Getting the Notification Response

If you want to get the push service response, you can call the method `getFeedback`:

    $push->getFeedback();

Or again, chain it to the above methods:

    $push->setMessage(['message'=>'This is the message','title'=>'This is the title'])
                        ->setApiKey('Server-API-Key')
                        ->setDevicesToken(['deviceToken1','deviceToken2','deviceToken3'...])
                        ->send()
                        ->getFeedback();

It will return an object with the response.

### Check if there was any error sending the push notification

    if(isset($push->feedback->error)){
        ....
    }

### Get Unregistered Devices tokens

After sending a notification, you may retrieve the list of unregistered tokens

    $push->getUnregisteredDeviceTokens();

This method returns an array of unregistered tokens from the Push service provider. If there isn't any unregistered token, it will return an empty array.

### Laravel Alias Facade

After register the Alias Facade for this Package, you can use it like follows:

    PushNotification::setService('fcm')
                            ->setMessage(['message'=>'This is the message','title'=>'This is the title'])
                            ->setApiKey('Server-API-Key')
                            ->setDevicesToken(['deviceToken1','deviceToken2','deviceToken3'...])
                            ->send()
                            ->getFeedback();

It would return the Push Feedback of the Notification sent.
