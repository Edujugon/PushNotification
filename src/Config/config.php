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
      'passPhrase' => '123456',
      'passFile' => __DIR__ . '/iosCertificates/yourKey.pem',
      'dry_run' => false
  ]
];