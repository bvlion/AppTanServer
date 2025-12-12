<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Event;

use App\Application\Actions\Event\SaveEventsAction;
use App\Application\Service\SearchWord\EventIngestionService;
use App\Domain\SearchWordEvent\EventType;
use App\Domain\SearchWordEvent\SearchWordEvent;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;

class SaveEventsActionTest extends TestCase
{
  public function testReturnsBadRequestWhenPayloadIsNotArray(): void
  {
    $ingestion = $this->createMock(EventIngestionService::class);
    $ingestion->expects($this->never())->method('ingest');

    $action = new class(new NullLogger(), $ingestion) extends SaveEventsAction {
      protected function getFormData()
      {
        return 'invalid';
      }
    };

    $request = (new ServerRequestFactory())->createServerRequest('POST', '/events');
    $response = new Response();

    $result = $action($request, $response, []);
    $payload = json_decode((string)$result->getBody(), true);

    $this->assertSame(400, $result->getStatusCode());
    $this->assertSame(['statusCode' => 400, 'data' => ['error' => 'Invalid JSON']], $payload);
  }

  public function testIngestsValidEventsAndSkipsIncompleteOnes(): void
  {
    $ingested = [];
    $ingestion = $this->createMock(EventIngestionService::class);
    $ingestion->method('ingest')->willReturnCallback(function (SearchWordEvent $event) use (&$ingested) {
      $ingested[] = $event;
    });
    $ingestion->expects($this->once())->method('ingest');

    $action = new SaveEventsAction(new NullLogger(), $ingestion);

    $body = [
      ['packageName' => 'skip-me'],
      [
        'packageName' => 'pkg',
        'word' => 'word',
        'eventType' => EventType::Launch->value,
        'eventWeight' => '0.5',
        'context' => ['foo' => 'bar'],
      ]
    ];

    $request = (new ServerRequestFactory())->createServerRequest('POST', '/events')
      ->withParsedBody($body);
    $response = new Response();

    $result = $action($request, $response, []);
    $payload = json_decode((string)$result->getBody(), true);

    $this->assertSame(200, $result->getStatusCode());
    $this->assertSame(['statusCode' => 200, 'data' => ['status' => 'ok']], $payload);

    $this->assertCount(1, $ingested);
    $event = $ingested[0];
    $this->assertSame('pkg', $event->getPackageName());
    $this->assertSame('word', $event->getWord());
    $this->assertSame(EventType::Launch, $event->getEventType());
    $this->assertSame(0.5, $event->getEventWeight());
    $this->assertSame(['foo' => 'bar'], $event->getContext());
    $this->assertInstanceOf(\DateTime::class, $event->getTimestamp());
  }
}
