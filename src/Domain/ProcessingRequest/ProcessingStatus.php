<?php

declare(strict_types=1);

namespace App\Domain\ProcessingRequest;

enum ProcessingStatus: string
{
  case Waiting = 'waiting';
  case InProgress = 'in_progress';
  case Done = 'done';
  case Failed = 'failed';

  public static function fromString(string $value): self
  {
    return match ($value) {
      'waiting' => self::Waiting,
      'in_progress' => self::InProgress,
      'done' => self::Done,
      'failed' => self::Failed,
      default => throw new \InvalidArgumentException("Invalid status: $value")
    };
  }
}
