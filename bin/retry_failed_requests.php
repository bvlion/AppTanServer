#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Domain\ProcessingRequest\ProcessingRequestRepository;
use DI\ContainerBuilder;

require __DIR__ . '/../vendor/autoload.php';

// 環境変数読み込み（.env）
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// コンテナ初期化
$containerBuilder = new ContainerBuilder();

// 設定・依存定義・リポジトリ定義を読み込み
(require __DIR__ . '/../app/settings.php')($containerBuilder);
(require __DIR__ . '/../app/dependencies.php')($containerBuilder);
(require __DIR__ . '/../app/repositories.php')($containerBuilder);

// コンテナ構築
$container = $containerBuilder->build();

// --- コンテナから依存を取得 ---
$requestRepository = $container->get(ProcessingRequestRepository::class);
$failedRequests = $requestRepository->findFailed();
foreach ($failedRequests as $failedRequest) {
  $packageName = $failedRequest->getPackageName();
  $appName = $failedRequest->getAppName();

  echo "Retrying: $packageName - $appName\n";

  // 再試行を同期で実行（結果を確認するため）
  $cmd = sprintf(
    '%s %s/bin/process_request.php %s %s >> %s 2>&1 &',
    escapeshellarg($_ENV['PHP_PATH']),
    escapeshellarg($_ENV['ROOT_PATH']),
    escapeshellarg($packageName),
    escapeshellarg($appName),
    escapeshellarg(sprintf($_ENV['ROOT_PATH'] . '/logs/retry-batch-%s-%s.log', $packageName, $appName))
  );
  exec($cmd, $output, $exitCode);

  if ($exitCode !== 0) {
    echo "Still failed: $packageName - $appName\n";
    $message = sprintf(
      "*再試行失敗*: `%s`\nアプリ名: `%s`\n```\n%s\n```",
      $packageName,
      $appName,
      implode("\n", array_slice($output, -10)) // 最後の10行だけ送信
    );
    notifySlack($message);
  } else {
    echo "Recovery success: $packageName - $appName\n";
  }
}

function notifySlack(string $message): void
{
  $payload = json_encode([
    'text' => $message,
    `as_user` => true,
    'username' => 'AppTanServer Retry Failed Requests',
    'icon_emoji' => ':warning:',
    'channel' => '#server_api',
  ]);

  $ch = curl_init($_ENV['SLACK_WEBHOOK_URL']);
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
  ]);
  curl_exec($ch);
  curl_close($ch);
}
