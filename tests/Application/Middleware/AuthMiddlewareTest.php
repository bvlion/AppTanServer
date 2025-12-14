<?php

declare(strict_types=1);

namespace Tests\Application\Middleware;

use App\Application\Middleware\AuthMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\NullLogger;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class AuthMiddlewareTest extends TestCase
{
  private ResponseFactory $responseFactory;

  protected function setUp(): void
  {
    $this->responseFactory = new ResponseFactory();
    $_ENV['BEARER_TOKEN'] = 'secret';
  }

  public function testExcludedPathPassesThrough(): void
  {
    $middleware = new AuthMiddleware(new NullLogger());
    $request = (new ServerRequestFactory())->createServerRequest('GET', '/healthcheck');
    $handler = $this->createMock(RequestHandlerInterface::class);
    $handler->expects($this->once())->method('handle')->willReturn($this->responseFactory->createResponse(200));

    $response = $middleware->process($request, $handler);

    $this->assertSame(200, $response->getStatusCode());
  }

  public function testMissingAuthorizationThrowsUnauthorized(): void
  {
    $middleware = new AuthMiddleware(new NullLogger());
    $request = (new ServerRequestFactory())->createServerRequest('GET', '/protected');
    $handler = $this->createMock(RequestHandlerInterface::class);
    $handler->method('handle')->willReturn($this->responseFactory->createResponse(200));

    $this->expectException(HttpUnauthorizedException::class);
    $middleware->process($request, $handler);
  }

  public function testInvalidTokenThrowsForbidden(): void
  {
    $middleware = new AuthMiddleware(new NullLogger());
    $request = (new ServerRequestFactory())->createServerRequest('GET', '/protected')
      ->withHeader('Authorization', 'Bearer wrong');
    $handler = $this->createMock(RequestHandlerInterface::class);

    $this->expectException(HttpForbiddenException::class);
    $middleware->process($request, $handler);
  }

  public function testValidTokenPassesThrough(): void
  {
    $middleware = new AuthMiddleware(new NullLogger());
    $request = (new ServerRequestFactory())->createServerRequest('GET', '/protected')
      ->withHeader('Authorization', 'Bearer secret');
    $handler = new class ($this->responseFactory) implements RequestHandlerInterface {
      public function __construct(private ResponseFactory $factory)
      {
      }
      public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
      {
        return $this->factory->createResponse(200);
      }
    };

    $response = $middleware->process($request, $handler);

    $this->assertSame(200, $response->getStatusCode());
  }
}
