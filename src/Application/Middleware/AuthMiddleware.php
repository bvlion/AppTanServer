<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpUnauthorizedException;
use Psr\Log\LoggerInterface;

class AuthMiddleware implements Middleware
{
  private array $excludedPaths = [
    '/healthcheck',
  ];

  public function __construct(
    private LoggerInterface $logger
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function process(Request $request, RequestHandler $handler): Response
  {
    $path = rtrim($request->getUri()->getPath(), '/');

    foreach ($this->excludedPaths as $excluded) {
      if ($path === $excluded || str_starts_with($path, $excluded . '/')) {
        return $handler->handle($request);
      }
    }

    $authorizationHeader = $request->getHeaderLine('Authorization');

    if (empty($authorizationHeader) || !preg_match('/Bearer\s+(\S+)/', $authorizationHeader, $matches)) {
      $this->logger->info('authorizationHeader: ' . $authorizationHeader);
      throw new HttpUnauthorizedException($request);
    }

    $token = $matches[1];

    if ($token !== ($_ENV['BEARER_TOKEN'] ?? '')) {
      throw new HttpForbiddenException($request);
    }

    return $handler->handle($request);
  }
}
