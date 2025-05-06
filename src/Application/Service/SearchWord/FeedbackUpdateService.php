<?php

declare(strict_types=1);

namespace App\Application\Service\SearchWord;

use App\Domain\SearchWordEvent\SearchWordEvent;
use App\Domain\SearchWordFeedback\SearchWordFeedbackRepository;

class FeedbackUpdateService
{
  public function __construct(
    private SearchWordFeedbackRepository $feedbackRepository
  ) {}

  public function updateFromEvent(SearchWordEvent $event): void
  {
    $type = $event->getEventType();

    $map = [
      'add' => [1, 0, 0, 0],
      're_add' => [0, 1, 0, 0],
      'remove' => [0, 0, 1, 0],
      'launch' => [0, 0, 0, 1],
    ];

    [$a, $r, $d, $l] = $map[$type] ?? [0, 0, 0, 0];

    if ($a + $r + $d + $l > 0) {
      $this->feedbackRepository->incrementCounts(
        $event->getPackageName(),
        $event->getWord(),
        $a, $r, $d, $l
      );
    }
  }
}
