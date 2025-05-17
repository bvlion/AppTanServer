#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Application\Service\SearchWord\MasterProjectionService;
use App\Domain\ProcessingRequest\ProcessingRequestRepository;
use App\Domain\ProcessingRequest\ProcessingStatus;
use App\Domain\SearchWordEvent\EventType;
use App\Domain\SearchWordEvent\SearchWordEvent;
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

// --- CLI引数チェック ---
[$_, $packageName, $appName] = $argv + [null, null, null];

if (!$packageName || !$appName) {
  fwrite(STDERR, "Usage: php bin/process_request.php <packageName> <appName>\n");
  exit(1);
}

// --- コンテナから依存を取得 ---
$requestRepository = $container->get(ProcessingRequestRepository::class);
$masterProjectionService = $container->get(MasterProjectionService::class);

// --- 多重実行防止 ---
$requestRepository->insertIfNotExists($packageName, $appName);

if (!$requestRepository->lockAndMarkInProgress($packageName, $appName)) {
  echo "Already processing or done: $packageName - $appName\n";
  exit(0);
}

// --- 実行処理 ---
try {
  $event = new SearchWordEvent(
    id: null,
    packageName: $packageName,
    word: $appName,
    eventType: EventType::Init,
    context: null,
    timestamp: new \DateTime()
  );

  $masterProjectionService->updateFromInitOrRefresh($event);

  $requestRepository->updateStatus($packageName, $appName, ProcessingStatus::Done);
  echo "Done: $packageName - $appName\n";

} catch (Throwable $e) {
  $requestRepository->updateStatus($packageName, $appName, ProcessingStatus::Failed);
  fwrite(STDERR, "Error: {$e->getMessage()}\n");
  exit(1);
}
