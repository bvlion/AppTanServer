<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\SearchWord;

use App\Domain\SearchWordEvent\SearchWordEvent;
use App\Domain\SearchWordEvent\SearchWordEventRepository;
use PDO;

class PdoSearchWordEventRepository implements SearchWordEventRepository
{
  public function __construct(private PDO $pdo)
  {
  }

  public function save(SearchWordEvent $event): void
  {
    $sql = <<<SQL
      INSERT INTO search_word_events (
        package_name,
        word,
        event_type,
        event_weight,
        context,
        timestamp
      ) VALUES (
        :package_name,
        :word,
        :event_type,
        :event_weight,
        :context,
        :timestamp
      )
    SQL;

    $stmt = $this->pdo->prepare($sql);

    $contextJson = $event->getContext() !== null
    ? json_encode($event->getContext(), JSON_UNESCAPED_UNICODE)
    : null;

    $stmt->execute([
      ':package_name' => $event->getPackageName(),
      ':word' => $event->getWord(),
      ':event_type' => $event->getEventType()->value,
      ':event_weight' => $event->getEventWeight(),
      ':context' => $contextJson,
      ':timestamp' => $event->getTimestamp()->format('Y-m-d H:i:s'),
    ]);
  }
}
