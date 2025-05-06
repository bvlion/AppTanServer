<?php

declare(strict_types=1);

namespace App\Domain\SearchWordsMaster;

use JsonSerializable;

class SearchWordsMaster implements JsonSerializable
{
  private string $packageName;
  private string $word;
  private string $appName;
  private string $source;
  private ?\DateTime $createdAt;

  public function __construct(
    string $packageName,
    string $word,
    string $appName,
    string $source = 'ai_generated',
    ?\DateTime $createdAt = null
  ) {
    $this->packageName = $packageName;
    $this->word = $word;
    $this->appName = $appName;
    $this->source = $source;
    $this->createdAt = $createdAt;
  }

  public function getPackageName(): string { return $this->packageName; }
  public function getWord(): string { return $this->word; }
  public function getAppName(): string { return $this->appName; }
  public function getSource(): string { return $this->source; }
  public function getCreatedAt(): ?\DateTime { return $this->createdAt; }

  #[\ReturnTypeWillChange]
  public function jsonSerialize(): array
  {
    return [
      'packageName' => $this->packageName,
      'word' => $this->word,
      'app_name' => $this->appName,
      'source' => $this->source,
      'createdAt' => $this->createdAt?->format(\DateTime::ATOM),
    ];
  }
}
