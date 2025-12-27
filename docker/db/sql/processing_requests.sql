CREATE TABLE processing_requests (
  package_name VARCHAR(255) NOT NULL,
  app_name VARCHAR(255) NOT NULL,
  status ENUM('waiting', 'in_progress', 'done', 'failed') NOT NULL DEFAULT 'waiting',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (package_name, app_name)
);
