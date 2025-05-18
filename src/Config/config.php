<?php
/**
 * @see https://github.com/Edujugon/PushNotification
 */

return [
    'gcm' => [
        'priority' => 'normal',
        'dry_run' => false,
        'apiKey' => 'My_ApiKey',
        // Optional: Default Guzzle request options for each GCM request
        // See https://docs.guzzlephp.org/en/stable/request-options.html
        'guzzle' => [],
    ],
    'fcm' => [
        'priority' => 'normal',
        'dry_run' => false,
        'apiKey' => 'My_ApiKey',
        // Optional: Default Guzzle request options for each FCM request
        // See https://docs.guzzlephp.org/en/stable/request-options.html
        'guzzle' => [],
    ],
    'apn' => [
        'certificate' => __DIR__ . '/iosCertificates/apns-dev-cert.pem',
        'passPhrase' => 'secret', //Optional
        'passFile' => __DIR__ . '/iosCertificates/yourKey.pem', //Optional
        'dry_run' => true,
    ],
    'apnp8' => [
        'key' => __DIR__ . '/apns-key.p8',
        'keyId' => 'My_Key_Id',
        'teamId' => 'My_Apple_Team_Id',
        'dry_run' => true,
    ],
];
