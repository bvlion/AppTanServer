<?php

declare(strict_types=1);

namespace Tests\Domain\SearchWordEvent;

use App\Domain\SearchWordEvent\EventType;
use App\Domain\SearchWordEvent\SearchWordEvent;
use PHPUnit\Framework\TestCase;

class SearchWordEventTest extends TestCase
{
  public function testJsonSerializeWithAllFields(): void
  {
    $timestamp = new \DateTime('2024-01-02 03:04:05', new \DateTimeZone('UTC'));

    $event = new SearchWordEvent(
      id: 42,
      packageName: 'com.example.app',
      word: 'Example',
      eventType: EventType::Init,
      eventWeight: 0.8,
      context: ['foo' => 'bar'],
      timestamp: $timestamp
    );

    $this->assertSame([
      'id' => 42,
      'packageName' => 'com.example.app',
      'word' => 'Example',
      'eventType' => EventType::Init->value,
      'eventWeight' => 0.8,
      'context' => ['foo' => 'bar'],
      'timestamp' => '2024-01-02T03:04:05+00:00',
    ], $event->jsonSerialize());
  }

  public function testJsonSerializeWithNullables(): void
  {
    $event = new SearchWordEvent(
      id: null,
      packageName: 'pkg',
      word: 'word',
      eventType: EventType::Launch,
      eventWeight: 1.0,
      context: null,
      timestamp: null
    );

    $this->assertSame([
      'id' => null,
      'packageName' => 'pkg',
      'word' => 'word',
      'eventType' => EventType::Launch->value,
      'eventWeight' => 1.0,
      'context' => null,
      'timestamp' => null,
    ], $event->jsonSerialize());
  }
}
