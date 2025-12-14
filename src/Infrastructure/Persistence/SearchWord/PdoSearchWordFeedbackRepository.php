<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\SearchWord;

use App\Domain\SearchWordFeedback\SearchWordFeedbackRepository;
use PDO;

class PdoSearchWordFeedbackRepository implements SearchWordFeedbackRepository
{
  public function __construct(private PDO $pdo)
  {
  }

  public function incrementCounts(
    string $packageName,
    string $word,
    int $added = 0,
    int $reAdded = 0,
    int $deleted = 0,
    int $launched = 0
  ): void {
    $sql = <<<SQL
      INSERT INTO search_word_feedback (
        package_name, word, added_count, re_added_count, deleted_count, launch_count
      ) VALUES (
        :package_name, :word, :added, :re_added, :deleted, :launched
      )
      ON DUPLICATE KEY UPDATE
        added_count = added_count + VALUES(added_count),
        re_added_count = re_added_count + VALUES(re_added_count),
        deleted_count = deleted_count + VALUES(deleted_count),
        launch_count = launch_count + VALUES(launch_count),
        updated_at = CURRENT_TIMESTAMP
    SQL;

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
      ':package_name' => $packageName,
      ':word' => $word,
      ':added' => $added,
      ':re_added' => $reAdded,
      ':deleted' => $deleted,
      ':launched' => $launched,
    ]);
  }
}
