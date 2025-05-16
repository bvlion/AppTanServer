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

    match (true) {
      $event->getEventType()->isForMasterUpdate() => $this->masterService->updateFromInitOrRefresh($event),
      $event->getEventType()->isForWordGeneration() => $this->masterService->registerGeneratedWord($event),
      $event->getEventType()->isForFeedback() => $this->feedbackService->updateFromEvent($event),
      default => null,
    };
  }
}
