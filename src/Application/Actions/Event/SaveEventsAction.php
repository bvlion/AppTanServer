<?php

declare(strict_types=1);

namespace App\Application\Actions\Event;

use App\Application\Actions\Action;
use App\Application\Actions\ActionPayload;
use App\Application\Service\SearchWord\EventIngestionService;
use App\Domain\SearchWordEvent\SearchWordEvent;
use Slim\Psr7\Response;
use Psr\Log\LoggerInterface;

class SaveEventsAction extends Action
{
  protected EventIngestionService $eventIngestionService;

  public function __construct(LoggerInterface $logger, EventIngestionService $eventIngestionService)
  {
    parent::__construct($logger);
    $this->eventIngestionService = $eventIngestionService;
  }

  /**
   * {@inheritdoc}
   */
  protected function action(): Response
  {
    $body = $this->getFormData();

    if (!is_array($body)) {
      return $this->respond(new ActionPayload(400, ['error' => 'Invalid JSON']));
    }

    foreach ($body as $entry) {
      if (!isset($entry['packageName'], $entry['word'], $entry['eventType'])) {
        continue; // スキップしても良いし、400で返してもOK
      }

      $event = new SearchWordEvent(
        id: null,
        packageName: $entry['packageName'],
        word: $entry['word'],
        eventType: $entry['eventType'],
        eventWeight: isset($entry['eventWeight']) ? (float)$entry['eventWeight'] : 1.0,
        context: isset($entry['context']) ? (array)$entry['context'] : null,
        timestamp: new \DateTime()
      );

      $this->eventIngestionService->ingest($event);
    }

    return $this->respond(new ActionPayload(
      statusCode: 200, data: ['status' => 'ok']
    ));
  }
}
