<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\SearchWord;

use App\Domain\SearchWordsMaster\SearchWordsMaster;
use App\Domain\SearchWordsMaster\SearchWordsMasterRepository;
use PDO;

class PdoSearchWordsMasterRepository implements SearchWordsMasterRepository
{
  public function __construct(private PDO $pdo) {}

  public function insert(SearchWordsMaster $master): void
  {
    $sql = <<<SQL
      INSERT INTO search_words_master (
        package_name, word, app_name, source
      ) VALUES (
        :package_name, :word, :app_name, :source
      )
    SQL;

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
      ':package_name' => $master->getPackageName(),
      ':word' => $master->getWord(),
      ':app_name' => $master->getAppName(),
      ':source' => $master->getSource(),
    ]);
  }

  public function exists(string $packageName, string $word, string $appName): bool
  {
    $sql = <<<SQL
      SELECT COUNT(*) FROM search_words_master
      WHERE package_name = :package_name
        AND word = :word
        AND app_name = :app_name
    SQL;

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
      ':package_name' => $packageName,
      ':word' => $word,
      ':app_name' => $appName,
    ]);

    return (int) $stmt->fetchColumn() > 0;
  }

  public function existsGeneratedWords(string $packageName, string $word): array
  {
    $sql = <<<SQL
      SELECT package_name, word, app_name, source, created_at
      FROM search_words_master
      WHERE package_name = :package_name
        AND word = :word
    SQL;

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
      ':package_name' => $packageName,
      ':word' => $word,
    ]);

    $results = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $results[] = new SearchWordsMaster(
        $row['package_name'],
        $row['word'],
        $row['app_name'],
        $row['source'],
        $row['created_at']
      );
    }

    return $results;
  }

  public function findByPackageAndAppName(string $packageName, string $appName): array
  {
    $sql = <<<SQL
      SELECT package_name, word, app_name, source
      FROM search_words_master
      WHERE package_name = :package_name AND app_name = :app_name
      ORDER BY created_at DESC
    SQL;

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
      ':package_name' => $packageName,
      ':app_name' => $appName,
    ]);

    $results = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $results[] = new SearchWordsMaster(
        $row['package_name'],
        $row['word'],
        $row['app_name'],
        $row['source'],
      );
    }

    return $results;
  }
}
