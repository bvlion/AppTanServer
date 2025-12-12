<?php

declare(strict_types=1);

namespace Tests\Domain\SearchWordEvent;

use App\Domain\SearchWordEvent\EventType;
use PHPUnit\Framework\TestCase;

class EventTypeTest extends TestCase
{
  /** @dataProvider masterUpdateProvider */
  public function testIsForMasterUpdate(EventType $eventType, bool $expected): void
  {
    $this->assertSame($expected, $eventType->isForMasterUpdate());
  }

  public function masterUpdateProvider(): array
  {
    return [
      [EventType::Init, true],
      [EventType::Refresh, true],
      [EventType::AiGenerated, false],
      [EventType::Imported, false],
      [EventType::Add, false],
      [EventType::ReAdd, false],
      [EventType::Remove, false],
      [EventType::Launch, false],
      [EventType::ScrapingInit, false],
    ];
  }

  /** @dataProvider wordGenerationProvider */
  public function testIsForWordGeneration(EventType $eventType, bool $expected): void
  {
    $this->assertSame($expected, $eventType->isForWordGeneration());
  }

  public function wordGenerationProvider(): array
  {
    return [
      [EventType::Init, false],
      [EventType::Refresh, false],
      [EventType::AiGenerated, true],
      [EventType::Imported, true],
      [EventType::Add, false],
      [EventType::ReAdd, false],
      [EventType::Remove, false],
      [EventType::Launch, false],
      [EventType::ScrapingInit, false],
    ];
  }

  /** @dataProvider feedbackProvider */
  public function testIsForFeedback(EventType $eventType, bool $expected): void
  {
    $this->assertSame($expected, $eventType->isForFeedback());
  }

  public function feedbackProvider(): array
  {
    return [
      [EventType::Init, false],
      [EventType::Refresh, false],
      [EventType::AiGenerated, false],
      [EventType::Imported, false],
      [EventType::Add, true],
      [EventType::ReAdd, true],
      [EventType::Remove, true],
      [EventType::Launch, true],
      [EventType::ScrapingInit, false],
    ];
  }

  /** @dataProvider feedbackCountsProvider */
  public function testGetFeedbackCounts(EventType $eventType, array $expectedCounts): void
  {
    $this->assertSame($expectedCounts, $eventType->getFeedbackCounts());
  }

  public function feedbackCountsProvider(): array
  {
    return [
      [EventType::Add, ['add' => 1, 're_add' => 0, 'remove' => 0, 'launch' => 0]],
      [EventType::ReAdd, ['add' => 0, 're_add' => 1, 'remove' => 0, 'launch' => 0]],
      [EventType::Remove, ['add' => 0, 're_add' => 0, 'remove' => 1, 'launch' => 0]],
      [EventType::Launch, ['add' => 0, 're_add' => 0, 'remove' => 0, 'launch' => 1]],
      [EventType::Init, ['add' => 0, 're_add' => 0, 'remove' => 0, 'launch' => 0]],
      [EventType::Refresh, ['add' => 0, 're_add' => 0, 'remove' => 0, 'launch' => 0]],
      [EventType::AiGenerated, ['add' => 0, 're_add' => 0, 'remove' => 0, 'launch' => 0]],
      [EventType::Imported, ['add' => 0, 're_add' => 0, 'remove' => 0, 'launch' => 0]],
      [EventType::ScrapingInit, ['add' => 0, 're_add' => 0, 'remove' => 0, 'launch' => 0]],
    ];
  }
}
