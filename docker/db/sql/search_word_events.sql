-- イベント履歴テーブル
CREATE TABLE search_word_events (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  package_name VARCHAR(255) NOT NULL,
  word VARCHAR(255) NOT NULL,
  event_type ENUM('init', 'refresh', 'add', 're_add', 'remove', 'launch', 'ai_generated', 'imported') NOT NULL,
  event_weight FLOAT DEFAULT 1.0,
  context JSON DEFAULT NULL,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_package_word_event (package_name, word, event_type)
);
