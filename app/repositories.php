<?php

declare(strict_types=1);

use App\Domain\SearchWordEvent\SearchWordEventRepository;
use App\Infrastructure\Persistence\SearchWord\PdoSearchWordEventRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
  $containerBuilder->addDefinitions([
    SearchWordEventRepository::class => \DI\autowire(PdoSearchWordEventRepository::class),
  ]);
};
