# 検索ワード API

## get /healthcheck

DB とアプリの稼働確認を行う

### request

内容 | 値 | 説明
:--|:--|:--
(なし) | - | ボディ不要

```
http://localhost:8080/healthcheck
```

### response

稼働状況

内容 | 値 | 説明
:--|:--|:--
db_time | string | DB 現在時刻（MySQL NOW()）
status | ok | 固定値

```
{
  "db_time": "2024-12-13 10:52:00",
  "status": "ok"
}
```

## post /events

検索ワード関連のイベントをまとめて登録し、イベント種別に応じて処理を実行する  
init および refresh では非同期でマスター生成を実行

### request

ボディは JSON 配列

内容 | 値 | 説明
:--|:--|:--
packageName | string | アプリのパッケージ名
word | string | イベント対象の単語（init/refresh ではアプリ名）
eventType | init, refresh, ai_generated, imported, add, re_add, remove, launch, scraping-init | イベント種別
eventWeight | number (0-1) | 重み（未指定時は 1.0）
context | object | 追加情報（例: ai_generated/imported では app_name・kana、scraping-init では description 等）

```
http://localhost:8080/events
```

例:
```
[
  {
    "packageName": "com.example.app",
    "word": "Example App",
    "eventType": "init"
  },
  {
    "packageName": "com.example.app",
    "word": "例ワード",
    "eventType": "add",
    "eventWeight": 0.8,
    "context": { "source": "user" }
  }
]
```

### response

イベント保存結果

内容 | 値 | 説明
:--|:--|:--
status | ok | 処理結果

```
{
  "status": "ok"
}
```

## post /masters/batch

パッケージ名・アプリ名の組をまとめて渡し、検索ワードマスターを取得する

### request

ボディは JSON 配列

内容 | 値 | 説明
:--|:--|:--
packageName | string | アプリのパッケージ名
appName | string | アプリ名

```
http://localhost:8080/masters/batch
```

例:
```
[
  { "packageName": "com.example.app", "appName": "Example App" },
  { "packageName": "com.example.other", "appName": "Other App" }
]
```

### response

指定パッケージごとのマスター一覧

内容 | 値 | 説明
:--|:--|:--
packageName | array | パッケージごとのマスター一覧（該当なしなら空配列）
word | string | 検索ワード
kana | string | 読み
appName | string | アプリ名

```
{
  "com.example.app": [
    { "word": "例ワード1", "kana": "レイワード1", "appName": "Example App" },
    { "word": "例ワード2", "kana": "レイワード2", "appName": "Example App" }
  ],
  "com.example.other": []
}
```
