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
          'host' =>    getenv('DB_HOST') ?: 'db',
          'port' =>    getenv('DB_PORT') ?: '3306',
          'dbname' =>  getenv('DB_NAME') ?: 'at',
          'user' =>    getenv('DB_USER') ?: 'user',
          'pass' =>    getenv('DB_PASS') ?: 'password',
          'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
        ],
      ]);
    }
  ]);
};
