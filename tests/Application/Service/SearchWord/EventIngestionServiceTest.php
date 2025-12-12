<?php

declare(strict_types=1);

namespace App\Application\Service\SearchWord;

function exec(string $command): void
{
  $GLOBALS['__event_ingestion_exec'][] = $command;
}

namespace Tests\Application\Service\SearchWord;

use App\Application\Service\SearchWord\EventIngestionService;
use App\Application\Service\SearchWord\FeedbackUpdateService;
use App\Application\Service\SearchWord\MasterProjectionService;
use App\Domain\SearchWordEvent\EventType;
use App\Domain\SearchWordEvent\SearchWordEvent;
use App\Domain\SearchWordEvent\SearchWordEventRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventIngestionServiceTest extends TestCase
{
  private SearchWordEventRepository|MockObject $repository;
  private MasterProjectionService|MockObject $masterService;
  private FeedbackUpdateService|MockObject $feedbackService;
  private EventIngestionService $service;

  protected function setUp(): void
  {
    $_ENV['PHP_PATH'] = '/usr/bin/php';
    $_ENV['ROOT_PATH'] = '/tmp';
    $GLOBALS['__event_ingestion_exec'] = [];

    $this->repository = $this->createMock(SearchWordEventRepository::class);
    $this->masterService = $this->createMock(MasterProjectionService::class);
    $this->feedbackService = $this->createMock(FeedbackUpdateService::class);

    $this->service = new EventIngestionService(
      $this->repository,
      $this->masterService,
      $this->feedbackService
    );
  }

  public function testMasterUpdateEventsTriggerCli(): void
  {
    $event = new SearchWordEvent(
      id: null,
      packageName: 'com.example.app',
      word: 'ExampleApp',
      eventType: EventType::Init,
      eventWeight: 1.0,
      context: null,
      timestamp: new \DateTime()
    );

    $this->repository->expects($this->once())->method('save')->with($event);
    $this->masterService->expects($this->never())->method('registerGeneratedWord');
    $this->feedbackService->expects($this->never())->method('updateFromEvent');

    $this->service->ingest($event);

    $this->assertCount(1, $GLOBALS['__event_ingestion_exec']);
    $command = $GLOBALS['__event_ingestion_exec'][0];
    $this->assertStringContainsString('com.example.app', $command);
    $this->assertStringContainsString('ExampleApp', $command);
  }

  public function testWordGenerationEventsAreDelegated(): void
  {
    $event = new SearchWordEvent(
      id: null,
      packageName: 'pkg',
      word: 'generated',
      eventType: EventType::Imported,
      eventWeight: 0.4,
      context: ['app_name' => 'AppName', 'kana' => 'かな'],
      timestamp: new \DateTime()
    );

    $this->repository->expects($this->once())->method('save')->with($event);
    $this->masterService->expects($this->once())->method('registerGeneratedWord')->with($event);
    $this->feedbackService->expects($this->never())->method('updateFromEvent');

    $this->service->ingest($event);

    $this->assertSame([], $GLOBALS['__event_ingestion_exec']);
  }

  public function testFeedbackEventsUpdateCounts(): void
  {
    $event = new SearchWordEvent(
      id: null,
      packageName: 'pkg',
      word: 'word',
      eventType: EventType::Remove,
      eventWeight: 1.0,
      context: null,
      timestamp: new \DateTime()
    );

    $this->repository->expects($this->once())->method('save')->with($event);
    $this->masterService->expects($this->never())->method('registerGeneratedWord');
    $this->feedbackService->expects($this->once())->method('updateFromEvent')->with($event);

    $this->service->ingest($event);

    $this->assertSame([], $GLOBALS['__event_ingestion_exec']);
  }
}
