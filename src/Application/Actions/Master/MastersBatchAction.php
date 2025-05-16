<?php

declare(strict_types=1);

namespace App\Application\Actions\Master;

use App\Application\Actions\Action;
use App\Application\Actions\ActionPayload;
use App\Domain\SearchWordsMaster\SearchWordsMasterRepository;
use Psr\Http\Message\ResponseInterface as Response;

class MastersBatchAction extends Action
{
  public function __construct(
    protected SearchWordsMasterRepository $repository
  ) {}

  protected function action(): Response
  {
    $payload = $this->getFormData();
    if (!is_array($payload)) {
      return $this->respond(new ActionPayload(400, ['error' => 'Invalid request format']));
    }

    $results = [];

    foreach ($payload as $entry) {
      if (empty($entry['packageName']) || empty($entry['appName'])) {
        continue; // skip malformed entries
      }

      $masters = $this->repository->findByPackageAndAppName(
        $entry['packageName'],
        $entry['appName']
      );

      $results[$entry['packageName']] = array_map(fn($m) => [
        'word' => $m->getWord(),
        'kana' => $m->getKana(),
        'appName' => $m->getAppName(),
      ], $masters);
    }

    return $this->respond(new ActionPayload(
      statusCode: 200, data: $results
    ));
  }
}
