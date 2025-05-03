<?php

declare(strict_types=1);

namespace App\Application\Actions\HealthCheck;

use PDO;
use Slim\Psr7\Response;
use App\Application\Actions\Action;

class HealthCheckAction extends Action
{
  public function __construct(private PDO $pdo) {}
  /**
   * {@inheritdoc}
   */
  protected function action(): Response
  {
    $stmt = $this->pdo->query('SELECT NOW() AS `current_time`');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $response = new Response();
    $response->getBody()->write(json_encode([
      'db_time' => $result['current_time'] ?? null,
      'status' => 'ok',
    ], JSON_UNESCAPED_UNICODE));

    return $response->withHeader('Content-Type', 'application/json');
  }
}
