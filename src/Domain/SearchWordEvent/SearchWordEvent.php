<?php

declare(strict_types=1);

namespace App\Domain\SearchWordEvent;

use JsonSerializable;

class SearchWordEvent implements JsonSerializable
{
  private ?int $id;
  private string $packageName;
  private string $word;
  private string $eventType;
  private float $eventWeight;
  private ?array $context;
  private ?\DateTime $timestamp;

  public function __construct(
    ?int $id,
    string $packageName,
    string $word,
    string $eventType,
    float $eventWeight = 1.0,
    ?array $context = null,
    ?\DateTime $timestamp = null
  ) {
    $this->id = $id;
    $this->packageName = $packageName;
    $this->word = $word;
    $this->eventType = $eventType;
    $this->eventWeight = $eventWeight;
    $this->context = $context;
    $this->timestamp = $timestamp;
  }

  public function getId(): ?int { return $this->id; }
  public function getPackageName(): string { return $this->packageName; }
  public function getWord(): string { return $this->word; }
  public function getEventType(): string { return $this->eventType; }
  public function getEventWeight(): float { return $this->eventWeight; }
  public function getContext(): ?array { return $this->context; }
  public function getTimestamp(): ?\DateTime { return $this->timestamp; }

  #[\ReturnTypeWillChange]
  public function jsonSerialize(): array
  {
    return [
      'id' => $this->id,
      'packageName' => $this->packageName,
      'word' => $this->word,
      'eventType' => $this->eventType,
      'eventWeight' => $this->eventWeight,
      'context' => $this->context,
      'timestamp' => $this->timestamp?->format(\DateTime::ATOM),
    ];
  }
}
