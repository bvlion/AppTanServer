<?php

declare(strict_types=1);

namespace App\Application\Service\SearchWord;

use App\Domain\SearchWordEvent\SearchWordEvent;
use App\Domain\SearchWordEvent\SearchWordEventRepository;
use App\Domain\SearchWordsMaster\SearchWordsMaster;
use App\Domain\SearchWordsMaster\SearchWordsMasterRepository;

class MasterProjectionService
{
  public function __construct(
    private SearchWordEventRepository $eventRepository,
    private SearchWordsMasterRepository $masterRepository,
    private AIWordGenerator $generator
  ) {}

  public function updateFromInitOrRefresh(SearchWordEvent $event): void
  {
    $package = $event->getPackageName();
    $appName = $event->getWord();
    $description = $event->getContext()['description'] ?? null;

    if (!$this->masterRepository->existsGeneratedWords($package, $appName)) {
      $words = $this->generator->generateWords($package, $appName, $description);
      foreach ($words as $word) {
        $this->masterRepository->insert(new SearchWordsMaster(
          packageName: $package,
          word: $word['word'],
          appName: $appName
        ));
        $this->eventRepository->save(new SearchWordEvent(
          id: null,
          packageName: $package,
          word: $word['word'],
          eventType: 'ai_generated',
          eventWeight: $word['weight'] / 100,
          context: ['app_name' => $appName],
          timestamp: new \DateTime()
        ));
      }
    }
  }

  public function registerGeneratedWord(SearchWordEvent $event): void
  {
    $package = $event->getPackageName();
    $word = $event->getWord();
    $appName = $event->getContext()['app_name'] ?? null;

    if (!$this->masterRepository->exists($package, $word, $appName)) {
      $this->masterRepository->insert(new SearchWordsMaster(
        packageName: $package,
        word: $word,
        appName: $appName,
        source: 'imported'
      ));
    }
  }
}
