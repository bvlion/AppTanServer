<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
  $containerBuilder->addDefinitions([
    SettingsInterface::class => function () {
      return new Settings([
        'displayErrorDetails' => true,
        'logError'            => false,
        'logErrorDetails'     => false,
        'logger' => [
          'name' =>  'slim-app',
          'path' =>  isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
          'level' => Logger::DEBUG,
        ],
        'db' => [
          'host' =>    $_ENV['DB_HOST'],
          'port' =>    $_ENV['DB_PORT'],
          'dbname' =>  $_ENV['DB_NAME'],
          'user' =>    $_ENV['DB_USER'],
          'pass' =>    $_ENV['DB_PASS'],
          'charset' => $_ENV['DB_CHARSET'],
        ],
      ]);
    }
  ]);
};
