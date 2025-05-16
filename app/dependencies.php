<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use App\Application\Service\SearchWord\{
    EventIngestionService,
    FeedbackUpdateService,
    MasterProjectionService,
    AIWordGenerator,
    GcfCaller
};
use App\Domain\SearchWordEvent\SearchWordEventRepository;
use App\Domain\SearchWordFeedback\SearchWordFeedbackRepository;
use App\Domain\SearchWordsMaster\SearchWordsMasterRepository;
use App\Infrastructure\Persistence\SearchWord\{
    PdoSearchWordEventRepository,
    PdoSearchWordFeedbackRepository,
    PdoSearchWordsMasterRepository
};
use GuzzleHttp\Client;

return function (ContainerBuilder $containerBuilder) {
  $containerBuilder->addDefinitions([
    LoggerInterface::class => function (ContainerInterface $c) {
      $settings = $c->get(SettingsInterface::class);

      $loggerSettings = $settings->get('logger');
      $logger = new Logger($loggerSettings['name']);

      $processor = new UidProcessor();
      $logger->pushProcessor($processor);

      $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
      $logger->pushHandler($handler);

      return $logger;
    },
    PDO::class => function (ContainerInterface $c) {
      $settings = $c->get(SettingsInterface::class)->get('db');

      $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $settings['host'],
        $settings['dbname'],
        $settings['charset']
      );

      return new \PDO(
        $dsn,
        $settings['user'],
        $settings['pass'],
        [
          \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
          \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
          \PDO::ATTR_EMULATE_PREPARES => false,
        ]
      );
    },

    SearchWordEventRepository::class => \DI\autowire(PdoSearchWordEventRepository::class),
    SearchWordFeedbackRepository::class => \DI\autowire(PdoSearchWordFeedbackRepository::class),
    SearchWordsMasterRepository::class => \DI\autowire(PdoSearchWordsMasterRepository::class),

    Client::class => function () {
      return new Client();
    },

    GcfCaller::class => function (ContainerInterface $c) {
      return new GcfCaller(
        $_ENV['GCF_AUDIENCE'],
        $_ENV['RESOURCES_DIR'] . 'sa-caller.json',
        $c->get(Client::class)
      );
    },

    AIWordGenerator::class => function (ContainerInterface $c) {
      return new AIWordGenerator(
        $c->get(Client::class),
        $_ENV['OPENAI_API_KEY']
      );
    },

    FeedbackUpdateService::class => DI\autowire(),
    EventIngestionService::class => DI\autowire(),
    MasterProjectionService::class => DI\autowire(),
  ]);
};
