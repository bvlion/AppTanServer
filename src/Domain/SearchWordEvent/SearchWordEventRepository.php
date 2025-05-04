<?php

declare(strict_types=1);

namespace App\Domain\SearchWordEvent;

interface SearchWordEventRepository
{
  public function save(SearchWordEvent $event): void;
}
