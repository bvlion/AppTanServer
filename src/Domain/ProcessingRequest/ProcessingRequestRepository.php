<?php

declare(strict_types=1);

namespace App\Domain\ProcessingRequest;

interface ProcessingRequestRepository
{
  public function findFailed(): array;

  public function insertIfNotExists(string $packageName, string $appName): void;

  public function lockAndMarkInProgress(string $packageName, string $appName): bool;

  public function updateStatus(
    string $packageName,
    string $appName,
    ProcessingStatus $status
  ): void;
}

