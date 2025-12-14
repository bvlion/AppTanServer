<?php

declare(strict_types=1);

namespace App\Domain\SearchWordFeedback;

use JsonSerializable;

class SearchWordFeedback implements JsonSerializable
{
  private string $packageName;
  private string $word;
  private int $addedCount;
  private int $reAddedCount;
  private int $deletedCount;
  private int $launchCount;
  private ?\DateTime $createdAt;
  private ?\DateTime $updatedAt;

  public function __construct(
    string $packageName,
    string $word,
    int $addedCount = 0,
    int $reAddedCount = 0,
    int $deletedCount = 0,
    int $launchCount = 0,
    ?\DateTime $createdAt = null,
    ?\DateTime $updatedAt = null
  ) {
    $this->packageName = $packageName;
    $this->word = $word;
    $this->addedCount = $addedCount;
    $this->reAddedCount = $reAddedCount;
    $this->deletedCount = $deletedCount;
    $this->launchCount = $launchCount;
    $this->createdAt = $createdAt;
    $this->updatedAt = $updatedAt;
  }

  public function getPackageName(): string
  {
    return $this->packageName;
  }
  public function getWord(): string
  {
    return $this->word;
  }
  public function getAddedCount(): int
  {
    return $this->addedCount;
  }
  public function getReAddedCount(): int
  {
    return $this->reAddedCount;
  }
  public function getDeletedCount(): int
  {
    return $this->deletedCount;
  }
  public function getLaunchCount(): int
  {
    return $this->launchCount;
  }
  public function getCreatedAt(): ?\DateTime
  {
    return $this->createdAt;
  }
  public function getUpdatedAt(): ?\DateTime
  {
    return $this->updatedAt;
  }

  #[\ReturnTypeWillChange]
  public function jsonSerialize(): array
  {
    return [
      'packageName' => $this->packageName,
      'word' => $this->word,
      'addedCount' => $this->addedCount,
      'reAddedCount' => $this->reAddedCount,
      'deletedCount' => $this->deletedCount,
      'launchCount' => $this->launchCount,
      'createdAt' => $this->createdAt?->format(\DateTime::ATOM),
      'updatedAt' => $this->updatedAt?->format(\DateTime::ATOM),
    ];
  }
}
