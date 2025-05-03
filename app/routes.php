<?php

declare(strict_types=1);

use App\Application\Actions\HealthCheck\HealthCheckAction;
use Slim\App;

return function (App $app) {
  $app->get('/healthcheck', HealthCheckAction::class);
};
