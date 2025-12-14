<?php

declare(strict_types=1);

namespace App\Application\Service\SearchWord;

use App\Domain\SearchWordEvent\SearchWordEvent;
use App\Domain\SearchWordFeedback\SearchWordFeedbackRepository;

class FeedbackUpdateService
{
  public function __construct(
    private SearchWordFeedbackRepository $feedbackRepository
  ) {
  }

  public function updateFromEvent(SearchWordEvent $event): void
  {
    $counts = $event->getEventType()->getFeedbackCounts();
    if (array_sum($counts) > 0) {
      $this->feedbackRepository->incrementCounts(
        $event->getPackageName(),
        $event->getWord(),
        $counts['add'],
        $counts['re_add'],
        $counts['remove'],
        $counts['launch']
      );
    }
  }
}
