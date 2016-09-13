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
      'certificate' => __DIR__ . '/iosCertificates/ck.pem',
      'passPhrase' => '123456', //Optional
      'passFile' => __DIR__ . '/iosCertificates/yourKey.pem', //Optional
      'dry_run' => false
  ]
];