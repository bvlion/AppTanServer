<?php

declare(strict_types=1);

namespace App\Domain\SearchWordsMaster;

interface SearchWordsMasterRepository
{
  public function insert(SearchWordsMaster $master): void;

  /**
   * @return SearchWordsMaster[]
   */
  public function existsGeneratedWords(string $packageName, string $word): array;

  public function exists(string $packageName, string $word, string $appName): bool;

  /**
   * @return SearchWordsMaster[]
   */
  public function findByPackageAndAppName(string $packageName, string $appName): array;
}
