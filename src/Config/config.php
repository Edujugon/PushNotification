<?php

return [
  'gcm' => [
      'priority' => 'normal',
      'dry_run' => false
  ],
  'fcm' => [
        'priority' => 'normal',
        'dry_run' => false
  ],
  'apn' => [
      'certificate' => __DIR__ . '/iosCertificates/apns-dev-cert.pem',
      'passPhrase' => '', //Optional
      'passFile' => __DIR__ . '/iosCertificates/yourKey.pem', //Optional
      'dry_run' => true
  ]
];