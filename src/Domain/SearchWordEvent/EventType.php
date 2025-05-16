<?php

declare(strict_types=1);

namespace App\Domain\SearchWordEvent;

enum EventType: string
{
  // --- init・refresh系（マスターデータ更新トリガー）
  case Init = 'init';
  case Refresh = 'refresh';

  // --- AI・インポート系（語彙生成）
  case AiGenerated = 'ai_generated';
  case Imported = 'imported';

  // --- ユーザー操作（フィードバック用途）
  case Add = 'add';
  case ReAdd = 're_add';
  case Remove = 'remove';
  case Launch = 'launch';

  // --- その他（再生成用途）
  case ScrapingInit = 'scraping-init';

  // どのハンドラーで処理すべきかを分類する

  public function isForMasterUpdate(): bool
  {
    return match ($this) {
      self::Init, self::Refresh => true,
      default => false,
    };
  }

  public function isForWordGeneration(): bool
  {
    return match ($this) {
      self::AiGenerated, self::Imported => true,
      default => false,
    };
  }

  public function isForFeedback(): bool
  {
    return match ($this) {
      self::Add, self::ReAdd, self::Remove, self::Launch => true,
      default => false,
    };
  }

  // どのフィードバックをカウントするかを取得する
  public function getFeedbackCounts(): array
  {
    return match ($this) {
      self::Add    => ['add' => 1, 're_add' => 0, 'remove' => 0, 'launch' => 0],
      self::ReAdd  => ['add' => 0, 're_add' => 1, 'remove' => 0, 'launch' => 0],
      self::Remove => ['add' => 0, 're_add' => 0, 'remove' => 1, 'launch' => 0],
      self::Launch => ['add' => 0, 're_add' => 0, 'remove' => 0, 'launch' => 1],
      default      => ['add' => 0, 're_add' => 0, 'remove' => 0, 'launch' => 0],
    };
  }
}
