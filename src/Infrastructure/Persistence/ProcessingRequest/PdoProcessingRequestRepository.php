<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\ProcessingRequest;

use App\Domain\ProcessingRequest\ProcessingRequest;
use App\Domain\ProcessingRequest\ProcessingRequestRepository;
use App\Domain\ProcessingRequest\ProcessingStatus;
use PDO;

class PdoProcessingRequestRepository implements ProcessingRequestRepository
{
  public function __construct(private PDO $pdo)
  {
  }

  public function findFailed(): array
  {
    $stmt = $this->pdo->prepare("
      SELECT package_name, app_name, status, updated_at
      FROM processing_requests
      WHERE status = 'failed'
      ORDER BY updated_at ASC
    ");
    $stmt->execute();

    $results = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $results[] = new ProcessingRequest(
        packageName: $row['package_name'],
        appName: $row['app_name'],
        status: ProcessingStatus::fromString($row['status']),
        updatedAt: new \DateTime($row['updated_at'])
      );
    }

    return $results;
  }

  public function insertIfNotExists(string $packageName, string $appName): void
  {
    $stmt = $this->pdo->prepare("
      INSERT IGNORE INTO processing_requests (package_name, app_name, status)
      VALUES (:package, :app, 'waiting')
    ");
    $stmt->execute([
      'package' => $packageName,
      'app'   => $appName,
    ]);
  }

  public function lockAndMarkInProgress(string $packageName, string $appName): bool
  {
    $this->pdo->beginTransaction();

    // Lock対象を取得（存在しない可能性は insertIfNotExists で排除済みとする）
    $stmt = $this->pdo->prepare("
      SELECT status FROM processing_requests
      WHERE package_name = :package AND app_name = :app
      FOR UPDATE
    ");
    $stmt->execute([
      'package' => $packageName,
      'app'   => $appName,
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !in_array($row['status'], ['waiting', 'failed'], true)) {
      $this->pdo->rollBack();
      return false;
    }

      // ステータスを in_progress に更新
    $stmt = $this->pdo->prepare("
      UPDATE processing_requests
      SET status = 'in_progress', updated_at = NOW()
      WHERE package_name = :package AND app_name = :app
    ");
    $stmt->execute([
      'package' => $packageName,
      'app'   => $appName,
    ]);

    $this->pdo->commit();
    return true;
  }

  public function updateStatus(
    string $packageName,
    string $appName,
    ProcessingStatus $status
  ): void {
    $stmt = $this->pdo->prepare("
      UPDATE processing_requests
      SET status = :status, updated_at = NOW()
      WHERE package_name = :package AND app_name = :app
    ");
    $stmt->execute([
      'status' => $status->value,
      'package' => $packageName,
      'app' => $appName,
    ]);
  }
}
