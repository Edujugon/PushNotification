<?php

return [
  'gcm' => [
      'priority' => 'normal',
      'dry_run' => false
  ],
  'apn' => [
      'certificate' => __DIR__ . '/iosCertificates/ck.pem',
      'passPhrase' => '123456',
      'dry_run' => false
  ]
];