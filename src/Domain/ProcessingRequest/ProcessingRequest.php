<?php

declare(strict_types=1);

namespace App\Domain\ProcessingRequest;

use JsonSerializable;

class ProcessingRequest implements JsonSerializable
{
  public function __construct(
    private string $packageName,
    private string $appName,
    private ProcessingStatus $status,
    private ?\DateTime $updatedAt = null
  ) {}

  public function getPackageName(): string
  {
    return $this->packageName;
  }

  public function getAppName(): string
  {
    return $this->appName;
  }

  public function getStatus(): ProcessingStatus
  {
    return $this->status;
  }

  public function getUpdatedAt(): ?\DateTime
  {
    return $this->updatedAt;
  }

  public function jsonSerialize(): array
  {
    return [
      'packageName' => $this->packageName,
      'appName' => $this->appName,
      'status' => $this->status->value,
      'updatedAt' => $this->updatedAt?->format(DATE_ATOM),
    ];
  }
}

