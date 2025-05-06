<?php

declare(strict_types=1);

namespace App\Application\Service\SearchWord;

use App\Domain\SearchWordEvent\SearchWordEvent;
use App\Domain\SearchWordEvent\SearchWordEventRepository;

class EventIngestionService
{
  public function __construct(
    private SearchWordEventRepository $repository,
    private MasterProjectionService $masterService,
    private FeedbackUpdateService $feedbackService
  ) {}

  public function ingest(SearchWordEvent $event): void
  {
    $this->repository->save($event);

    match ($event->getEventType()) {
      'init', 'refresh' => $this->masterService->updateFromInitOrRefresh($event),
      'ai_generated', 'imported' => $this->masterService->registerGeneratedWord($event),
      'add', 're_add', 'remove', 'launch' => $this->feedbackService->updateFromEvent($event),
      default => null,
    };
  }
}
