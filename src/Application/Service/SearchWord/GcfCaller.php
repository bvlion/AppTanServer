<?php

declare(strict_types=1);

namespace App\Application\Service\SearchWord;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Google\Auth\ApplicationDefaultCredentials;

class GcfCaller
{
  private string $audience;
  private string $credentialsPath;
  private Client $client;

  public function __construct(string $audience, string $credentialsPath, Client $client)
  {
    $this->audience = $audience;
    $this->credentialsPath = $credentialsPath;
    $this->client = $client;
  }

  public function fetchDescriptionByPackageName($packageName): string
  {
    $idToken = '';
    $accessUrl = 'http://host.docker.internal:5001/apptan/us-central1/scrape';
    if (trim($this->audience) !== '') {
      putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $this->credentialsPath);
      $credentials = ApplicationDefaultCredentials::getIdTokenCredentials($this->audience);
      $authToken = $credentials->fetchAuthToken();
      $idToken = $authToken['id_token'];
      $accessUrl = $this->audience;
    }
    try {
      $response = $this->client->request('GET', $accessUrl, [
        'headers' => [
          'Authorization' => "Bearer $idToken",
          'Accept' => 'application/json',
        ],
        'query' => [
          'packageName' => $packageName
        ],
      ]);

      $json = json_decode($response->getBody()->getContents(), true);

      return $json['description'] ?? '';
    } catch (RequestException $e) {
      throw new \RuntimeException("GCF 呼び出しに失敗しました: " . $e->getMessage(), 0, $e);
    }
  }
}
