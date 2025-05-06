<?php

declare(strict_types=1);

namespace App\Domain\SearchWordFeedback;

interface SearchWordFeedbackRepository
{
  public function incrementCounts(
    string $packageName,
    string $word,
    int $added = 0,
    int $reAdded = 0,
    int $deleted = 0,
    int $launched = 0
  ): void;
}
