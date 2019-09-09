# PushNotification Package

[![Build Status](https://api.travis-ci.org/Edujugon/PushNotification.svg)](https://api.travis-ci.org/Edujugon/PushNotification)
[![Total Downloads](https://poser.pugx.org/edujugon/push-notification/downloads)](https://packagist.org/packages/edujugon/push-notification)
[![Latest Stable Version](https://poser.pugx.org/edujugon/push-notification/v/stable)](https://packagist.org/packages/edujugon/push-notification)
[![License](https://poser.pugx.org/edujugon/push-notification/license)](https://packagist.org/packages/edujugon/push-notification)

This is an easy to use package to send push notification.

#### Push Service Providers Available:

* GCM
* FCM
* APN

## Installation

### Laravel version below 5.8

type in console:

```
composer require "edujugon/push-notification:^v3.0.0"
```

### Laravel 5.8/6 and higher

type in console:

```
composer require edujugon/push-notification
```

The package will automatically register its service provider.

Publish the package's configuration file to the application's own config directory
```
php artisan vendor:publish --provider="Edujugon\PushNotification\Providers\PushNotificationServiceProvider" --tag="config"
```

> Go to [laravel facade sample](https://github.com/edujugon/PushNotification#laravel-alias-facade) directly.

### Configuration

After publishing the configuration, you can find the Push service config in config/pushnotification.php

The default configuration parameters for **GCM** and **FCM** are :

*   priority => normal
*   dry_run => false
*   apiKey => Your ApiKey

You can dynamically update those values or adding new ones calling the method setConfig like so:
```php
$push->setConfig([
    'priority' => 'high',
    'dry_run' => true,
    'time_to_live' => 3
]);
```


The default configuration parameters for **APN** are:

*   ```certificate => __DIR__ . '/iosCertificates/yourCertificate.pem'```
*   ```passPhrase => 'MyPassPhrase'```
*   ```passFile => __DIR__ . '/iosCertificates/yourKey.pem' //Optional```
*   ```dry_run => false```

(Make sure to set `dry_run` to `true` if you're using development *.pem certificate, and `false` for production)

Also you can update those values and add more dynamically
```php
$push->setConfig([
    'passPhrase' => 'NewPass',
    'custom' => 'MycustomValue',
    'dry_run' => true
]);
```

Even you may update the url of the Push Service dynamically like follows:
```php
$push->setUrl('http://newPushServiceUrl.com');
```

> Not update the url unless it's really necessary.

You can specify the number of client-side attempts to APN before giving
up.  The default amount is 3 attempts.  You can override this value by
specifying `connection_attempts` in `setConfig()` assoc-array.  Keep in
mind the default number of requested attempts is 3.

If you prefer to retry indefinitely, set `connection_attempts` to zero.

    $push->setConfig([
        'passPhrase' => 'NewPass',
        'custom' => 'MycustomValue',
        'connection_attempts' => 0,
        'dry_run' => true
    ]);


## Usage
```php
$push = new PushNotification;
```

By default it will use GCM as Push Service provider.

For APN Service:
```php
$push = new PushNotification('apn');
```

For FCM Service:
```php
$push = new PushNotification('fcm');
```

Now you may use any method that you need. Please see the API List.


## API List

- [setService](https://github.com/edujugon/PushNotification#setservice)
- [setMessage](https://github.com/edujugon/PushNotification#setmessage)
- [setDevicesToken](https://github.com/edujugon/PushNotification#setdevicestoken)
- [send](https://github.com/edujugon/PushNotification#send)
- [getFeedback](https://github.com/edujugon/PushNotification#getfeedback)
- [getUnregisteredDeviceTokens](https://github.com/edujugon/PushNotification#getunregistereddevicetokens)
- [setConfig](https://github.com/edujugon/PushNotification#setconfig)
- [setUrl](https://github.com/edujugon/PushNotification#seturl)

### Only for Gcm and Fcm

- [setApiKey](https://github.com/edujugon/PushNotification#setapikey)

### Only for Fcm

- [sendByTopic](https://github.com/edujugon/PushNotification#sendbytopic)

> Go to [Usage samples](https://github.com/edujugon/PushNotification#usage-samples) directly.

#### setService

`setService` method sets the push service to be used, which you pass the name through parameter as a string.

**Syntax**

```php
object setService($name)
```

#### setMessage

`setMessage` method sets the message parameters, which you pass the values through parameter as an array.

**Syntax**

```php
object setMessage(array $data)
```

#### setApiKey

> Only for gcm and fcm

`setApiKey` method sets the API Key of your App, which you pass the key through parameter as a string.

**Syntax**

```php
object setApiKey($api_key)
```

#### setDevicesToken

`setDevicesToken` method sets the devices' tokens, which you pass the token through parameter as array or string if it was only one.

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

#### sendByTopic

> Only for fcm

`sendBytopic` method sends a message by topic. It also accepts the topic condition. more details [here](https://firebase.google.com/docs/cloud-messaging/android/topic-messaging)
>If isCondition is true, $topic will be treated as an expression

**Syntax**

```php
object sendByTopic($topic,$isCondition)
```

### Usage samples

>You can chain the methods.

GCM sample:

```php
$push->setMessage([
        'notification' => [
                'title'=>'This is the title',
                'body'=>'This is the message',
                'sound' => 'default'
                ],
        'data' => [
                'extraPayLoad1' => 'value1',
                'extraPayLoad2' => 'value2'
                ]
        ])
        ->setApiKey('Server-API-Key')
        ->setDevicesToken(['deviceToken1','deviceToken2','deviceToken3'...]);
```

APN sample:
```php
$push->setMessage([
            'aps' => [
                'alert' => [
                    'title' => 'This is the title',
                    'body' => 'This is the body'
                ],
                'sound' => 'default',
                'badge' => 1

            ],
            'extraPayLoad' => [
                'custom' => 'My custom data',
            ]
        ])
    ->setDevicesToken(['deviceToken1','deviceToken2','deviceToken3'...]);
```
or do it separately
```php
$push->setMessage([
       'notification' => [
               'title'=>'This is the title',
               'body'=>'This is the message',
               'sound' => 'default'
               ],
       'data' => [
               'extraPayLoad1' => 'value1',
               'extraPayLoad2' => 'value2'
               ]
       ]);
$push->setApiKey('Server-API-Key');
$push->setDevicesToken(['deviceToken1'
    ,'deviceToken2',
    'deviceToken3'
]);
```
If you want send the notification to only 1 device, you may pass the value as string.
```php
$push->setDevicesToken('deviceToken');
```

### Send the Notification

Method send() can be also chained to the above methods.
```php
$push->setMessage([
       'notification' => [
               'title'=>'This is the title',
               'body'=>'This is the message',
               'sound' => 'default'
               ],
       'data' => [
               'extraPayLoad1' => 'value1',
               'extraPayLoad2' => 'value2'
               ]
       ])
    ->setApiKey('Server-API-Key')
    ->setDevicesToken(['deviceToken1','deviceToken2','deviceToken3'...])
    ->send();
```

### Send the Notification by Topic (**FCM** only)

```php
$push = new PushNotification('fcm');
$response = $push->setMessage(['message'=>'Hello World'])
            ->setApiKey('YOUR-API-KEY')
            ->setConfig(['dry_run' => false])
            ->sendByTopic('dogs');
```

or with a condition:
```php
$push = new PushNotification('fcm');
$response = $push->setMessage(['message'=>'Hello World'])
            ->setApiKey('YOUR-API-KEY')
            ->setConfig(['dry_run' => false])
            ->sendByTopic("'dogs' in topics || 'cats' in topics",true);
```

### Understanding Gcm and Fcm Message Payload

#### Notification Message

Add a `notification` key when setting the message in `setMessage` method. like follows:

```php
$push->setMessage([
           'notification' => [
                   'title'=>'This is the title',
                   'body'=>'This is the message',
                   'sound' => 'default'
                   ]
           );
```

You may add some extra payload adding a `data` key when setting the message in `setMessage` method.

```php
$push->setMessage([
           'notification' => [
                   'title'=>'This is the title',
                   'body'=>'This is the message',
                   'sound' => 'default'
                   ],
           'data' => [
                   'extraPayLoad1' => 'value1',
                   'extraPayLoad2' => 'value2'
                   ]
           ]);
```

#### Data Message

By default, this package sends the notification as Data Message. So no need to add a `data` key.

```php
$push->setMessage([
           'title'=>'This is the title',
           'body'=>'This is the message',
           'myCustomVAlue' => 'value'
       ]);
```

The above example is like you were sending the following:

```php
$push->setMessage([
           'data' => [
                   'title'=>'This is the title',
                  'body'=>'This is the message',
                  'myCustomVAlue' => 'value'
                   ]
           ]);
```

For more details, have a look at [gcm/fcm notification paypload support](https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support) and [the concept options](https://firebase.google.com/docs/cloud-messaging/concept-options)

### Getting the Notification Response

If you want to get the push service response, you can call the method `getFeedback`:
```php
    $push->getFeedback();
```

Or again, chain it to the above methods:
```php
    $push->setMessage(['body'=>'This is the message','title'=>'This is the title'])
                        ->setApiKey('Server-API-Key')
                        ->setDevicesToken(['deviceToken1','deviceToken2','deviceToken3'...])
                        ->send()
                        ->getFeedback();
```

It will return an object with the response.

### APN Server Feedback and package Feedback

Any time you send a notification, it will check if APN server has any feedback for your certificate.
If so, the responses are merged to our feedback like below:

```php
class stdClass#21 (4) {
  public $success =>
  int(0)
  public $failure =>
  int(1)
  public $tokenFailList =>
  array(1) {
    [0] =>
    string(64) "c55741656e6c3185f3474291aebb5cf878b8719288e52bf4c497292b320312c5"
  }
  public $apnsFeedback =>
  array(1) {
    [0] =>
    class stdClass#16 (3) {
      public $timestamp =>
      int(1478272639)
      public $length =>
      int(32)
      public $devtoken =>
      string(64) "c55741656e6c3185f3474291aebb5cf878b8719288e52bf4c497292b320312c5"
    }
  }
}

```

### Get Unregistered Devices tokens

After sending a notification, you may retrieve the list of unregistered tokens
```php
$push->getUnregisteredDeviceTokens();
```

This method returns an array of unregistered tokens from the Push service provider. If there isn't any unregistered token, it will return an empty array.

### Laravel Alias Facade

After register the Alias Facade for this Package, you can use it like follows:
```php
PushNotification::setService('fcm')
                        ->setMessage([
                             'notification' => [
                                     'title'=>'This is the title',
                                     'body'=>'This is the message',
                                     'sound' => 'default'
                                     ],
                             'data' => [
                                     'extraPayLoad1' => 'value1',
                                     'extraPayLoad2' => 'value2'
                                     ]
                             ])
                        ->setApiKey('Server-API-Key')
                        ->setDevicesToken(['deviceToken1','deviceToken2','deviceToken3'...])
                        ->send()
                        ->getFeedback();
```

It would return the Push Feedback of the Notification sent.

### Notification channels

#### Formatting Push Notifications
If a notification supports being sent as an push message, you should define  `toApn` and/or `toFcm`/`toGcm` methods on the notification class. This method will receive a `$notifiable` entity and should return a  `Edujugon\PushNotification\Messages\PushMessage` instance:

```php
public function toApn($notifiable)
{
    return new PushMessage('Hello world');
}
```

#### Customizing The Title and Body
```php
public function toApn($notifiable)
{
    return (new PushMessage)
        ->title('Hello world')
        ->body('...');
}
```

#### Customizing The Notification Sound
```php
public function toApn($notifiable)
{
    return (new PushMessage)
        ->body('Hello world')
        ->sound('default');
}
```

#### Customizing The Badge Number
```php
public function toApn($notifiable)
{
  return (new PushMessage)
        ->body('Hello world')
        ->sound('default')
        ->badge(7);
}
```

#### Passing Service Config
```php
public function toApn($notifiable)
{
    return (new PushMessage)
        ->body('Hello world')
        ->config(['dry_run' => false]);
}
```

#### Add it to the notification channels
```php
public function via($notifiable)
{
    return [ApnChannel::class];
}
```
>Don't forget the use statement at the top of the class

#### Routing Push Notifications
Just define `routeNotificationForApn` and/or `routeNotificationForFcm`/`routeNotificationForGcm` methods on the entity
```php
/**
 * Route notifications for the Apn channel.
 *
 * @return string|array
 */
public function routeNotificationForApn()
{
    return $this->ios_push_token;
}
```
