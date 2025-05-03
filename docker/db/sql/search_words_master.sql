-- 初期辞書マスターテーブル（AI or imported語句のみ）
CREATE TABLE search_words_master (
  package_name VARCHAR(255) NOT NULL,
  word VARCHAR(255) NOT NULL,
  label VARCHAR(255) NOT NULL,
  source ENUM('ai_generated', 'imported') DEFAULT 'ai_generated',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (package_name, word, label)
);
