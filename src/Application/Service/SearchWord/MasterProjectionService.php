<?php

declare(strict_types=1);

namespace App\Application\Service\SearchWord;

use App\Domain\SearchWordEvent\EventType;
use App\Domain\SearchWordEvent\SearchWordEvent;
use App\Domain\SearchWordEvent\SearchWordEventRepository;
use App\Domain\SearchWordsMaster\SearchWordsMaster;
use App\Domain\SearchWordsMaster\SearchWordsMasterRepository;

class MasterProjectionService
{
  public function __construct(
    private SearchWordEventRepository $eventRepository,
    private SearchWordsMasterRepository $masterRepository,
    private AIWordGenerator $generator,
    private GcfCaller $gcfCaller
  ) {}

  public function updateFromInitOrRefresh(SearchWordEvent $event): void
  {
    $package = $event->getPackageName();
    $appName = $event->getWord();
    $description = $event->getContext()['description'] ?? null;

    if (!$this->masterRepository->existsGeneratedWords($package, $appName)) {
      $words = $this->generator->generateWords($package, $appName, $description);
      $hasLowWeight = false;
      foreach ($words as $word) {
        if ($word['weight'] <= 65) {
          $hasLowWeight = true;
          break;
        }
      }
      // 2つの読みと5個未満の単語と、タイトルとパッケージだけでは生成できない場合は1度スクレイピングする
      if ((count($words) < 7 || $hasLowWeight) && !$event->getEventType() !== 'scraping-init') {
        $newEvent = new SearchWordEvent(
          id: null,
          packageName: $package,
          word: $appName,
          eventType: EventType::ScrapingInit,
          context: ['description' => $this->gcfCaller->fetchDescriptionByPackageName($package)],
          timestamp: new \DateTime()
        );
        $this->eventRepository->save($newEvent);
        $this->updateFromInitOrRefresh($newEvent);
        return;
      }
      foreach ($words as $word) {
        $kana = $this->getKana($word['word']);
        $this->masterRepository->insert(new SearchWordsMaster(
          packageName: $package,
          word: $word['word'],
          kana: $kana,
          appName: $appName
        ));
        $this->eventRepository->save(new SearchWordEvent(
          id: null,
          packageName: $package,
          word: $word['word'],
          eventType: EventType::AiGenerated,
          eventWeight: $word['weight'] / 100,
          context: ['app_name' => $appName, 'kana' => $kana],
          timestamp: new \DateTime()
        ));
      }
    }
  }

  public function registerGeneratedWord(SearchWordEvent $event): void
  {
    $package = $event->getPackageName();
    $word = $event->getWord();
    $appName = $event->getContext()['app_name'] ?? '';
    $kana = $event->getContext()['kana'] ?? '';

    if (!$this->masterRepository->exists($package, $word, $appName)) {
      $this->masterRepository->insert(new SearchWordsMaster(
        packageName: $package,
        word: $word,
        kana: $kana,
        appName: $appName,
        source: 'imported'
      ));
    }
  }

  private function getKana(string $word): string
  {
    exec('echo ' . $word . ' | ' . $_ENV['MECAB'] . ' -Oyomi', $res);
    if (count($res) === 0) {
      return '';
    }
    return preg_replace_callback('/[ァ-ヶー]+/u', function ($matches) {
      return mb_convert_kana($matches[0], 'c', 'UTF-8');
    }, $res[0]);
  }
}
