<?php declare(strict_types=1);
use PN\Yaf\Core\Environment;

return [
  'environment' => Environment::DEV,

  'yaf' => [
    'event_subscribers' => [
      'YourApp\\Services\\YourEventSubscriber',
    ],
  ],
];
