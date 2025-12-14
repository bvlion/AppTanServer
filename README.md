# AppTanServer

検索ワードイベントを扱う Slim 4 ベースの API サーバーです。Docker Compose で MySQL と PHP コンテナを起動して利用します。

## 前提
- Docker / Docker Compose
- Make（任意、ショートカット用）

## セットアップ
1. `.env.sample` を `.env` にコピーし、DB・パス等を必要に応じて調整してください。
2. 依存インストール（Docker 内で実行）:
   ```sh
   make install     # docker compose up composer
   ```

## 起動・停止
- アプリ + DB 起動:
  ```sh
  make up          # db と slim コンテナをバックグラウンド起動
  ```
- 停止:
  ```sh
  make down
  ```

## 開発用コマンド
- 整形（2スペースインデント / phpcs ルール準拠）:
  ```sh
  make format
  ```
- コーディング規約チェック:
  ```sh
  make phpcs
  ```
- 静的解析:
  ```sh
  make phpstan
  ```
- テスト:
  ```sh
  make phpunit    # または make test
  ```

## API ドキュメント
- `docs/api.md` を参照してください。

## カバレッジ
- Coveralls で確認できます: https://coveralls.io/github/bvlion/AppTanServer

## 備考
- コーディングスタイルは PSR-12 ベースでインデントを 2 スペースにカスタマイズしています。`composer format` / `make format` で強制整形します。
- PHPUnit は `tests/bootstrap.php` / `phpunit.xml` を使用します。
- Docker 実行で権限やライセンス確認（Xcode など）が求められた場合は、指示に従って同意・権限付与の上で再度 `make` を実行してください。
