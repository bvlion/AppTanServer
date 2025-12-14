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
  ) {
  }

  public function ingest(SearchWordEvent $event): void
  {
    $this->repository->save($event);

    match (true) {
      $event->getEventType()->isForMasterUpdate() => $this->launchMasterProjectionCli($event),
      $event->getEventType()->isForWordGeneration() => $this->masterService->registerGeneratedWord($event),
      $event->getEventType()->isForFeedback() => $this->feedbackService->updateFromEvent($event),
      default => null,
    };
  }

  private function launchMasterProjectionCli(SearchWordEvent $event): void
  {
    $packageName = $event->getPackageName();
    $appName = $event->getWord();

    $cmd = sprintf(
      '%s %s/bin/process_request.php %s %s >> %s 2>&1 &',
      escapeshellarg($_ENV['PHP_PATH']),
      escapeshellarg($_ENV['ROOT_PATH']),
      escapeshellarg($packageName),
      escapeshellarg($appName),
      escapeshellarg(sprintf($_ENV['ROOT_PATH'] . '/logs/requests-%s-%s.log', $packageName, $appName))
    );

    exec($cmd);
  }
}
