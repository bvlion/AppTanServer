<?php

declare(strict_types=1);

namespace Tests\Application\Service\SearchWord;

use App\Application\Service\SearchWord\AIWordGenerator;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Utils;

class AIWordGeneratorTest extends TestCase
{
  private string $tmpRoot;

  protected function setUp(): void
  {
    $this->tmpRoot = sys_get_temp_dir() . '/ai-generator-' . uniqid();
    mkdir($this->tmpRoot, 0777, true);
    mkdir($this->tmpRoot . '/resources', 0777, true);
    file_put_contents($this->tmpRoot . '/resources/generate_search_words.txt', 'system prompt');
    $_ENV['ROOT_PATH'] = $this->tmpRoot;
  }

  protected function tearDown(): void
  {
    $files = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($this->tmpRoot, \FilesystemIterator::SKIP_DOTS),
      \RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $file) {
      if ($file->isDir()) {
        rmdir($file->getRealPath());
      } else {
        unlink($file->getRealPath());
      }
    }
    @rmdir($this->tmpRoot);
  }

  public function testGenerateWordsMergesMainAndReadings(): void
  {
    $response = $this->createMock(ResponseInterface::class);
    $response->method('getBody')->willReturn(Utils::streamFor(json_encode([
      'choices' => [
        [
          'message' => [
            'content' => json_encode([
              'mainWords' => [
                ['word' => '主要', 'weight' => 90],
              ],
              'readings' => [
                ['word' => 'しゅよう', 'weight' => 80],
              ],
            ])
          ]
        ]
      ]
    ])));

    $client = $this->createMock(Client::class);
    $client->method('post')->willReturn($response);

    $generator = new AIWordGenerator($client, 'dummy-key', 'dummy-model');

    $result = $generator->generateWords('App', 'pkg', 'desc');

    $this->assertSame([
      ['word' => '主要', 'weight' => 90],
      ['word' => 'しゅよう', 'weight' => 80],
    ], $result);
  }

  public function testGenerateWordsThrowsWhenContentMissing(): void
  {
    $response = $this->createMock(ResponseInterface::class);
    $response->method('getBody')->willReturn(Utils::streamFor(json_encode([
      'choices' => [['message' => []]],
    ])));

    $client = $this->createMock(Client::class);
    $client->method('post')->willReturn($response);

    $generator = new AIWordGenerator($client, 'dummy-key', 'dummy-model');

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('No content returned from OpenAI');

    $generator->generateWords('App', 'pkg', null);
  }
}
