-- スコア集計テーブル
CREATE TABLE search_word_feedback (
  package_name VARCHAR(255) NOT NULL,
  word VARCHAR(255) NOT NULL,
  added_count INT DEFAULT 0,
  re_added_count INT DEFAULT 0,
  deleted_count INT DEFAULT 0,
  launch_count INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (package_name, word)
);
