<?php

declare(strict_types=1);

namespace App\Application\Service\SearchWord;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AIWordGenerator
{
  private Client $client;
  private string $apiKey;

  public function __construct(Client $client, string $apiKey)
  {
    $this->client = $client;
    $this->apiKey = $apiKey;
  }

  /**
   * アプリ情報から searchWords を生成する
   *
   * @param string $appName
   * @param string $packageName
   * @param string|null $description
   * @return array [['word' => string, 'weight' => int], ...]
   * @throws \RuntimeException
   */
  public function generateWords(string $appName, string $packageName, ?string $description): array
  {
    $promptPath = $_ENV['ROOT_PATH'] . '/resources/generate_search_words.txt';
    $systemPrompt = file_get_contents($promptPath);
    if ($systemPrompt === false) {
      throw new \RuntimeException("プロンプトファイルの読み込みに失敗しました: {$promptPath}");
    }

    $userPrompt = "アプリ名: {$appName}\nパッケージ名: {$packageName}\n説明: " . ($description ?: "なし");

    try {
      $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
          'Authorization' => "Bearer {$this->apiKey}",
          'Content-Type'  => 'application/json',
        ],
        'json' => [
          'model' => 'gpt-4o-mini',
          'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
          ],
          'temperature' => 0.3,
          'max_tokens' => 300,
        ]
      ]);
    } catch (GuzzleException $e) {
      throw new \RuntimeException("OpenAI API request failed: " . $e->getMessage());
    }

    $body = json_decode((string)$response->getBody(), true);
    $content = $body['choices'][0]['message']['content'] ?? null;

    if (!$content) {
      throw new \RuntimeException('No content returned from OpenAI');
    }

    $json = json_decode($content, true);
    if (!is_array($json) || !isset($json['mainWords'], $json['readings'])) {
      throw new \RuntimeException('Unexpected response format from OpenAI');
    }

    return array_merge($json['mainWords'], $json['readings']);
  }
}
