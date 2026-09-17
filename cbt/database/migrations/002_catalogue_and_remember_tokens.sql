ALTER TABLE tests
 ADD COLUMN exam_name VARCHAR(120) NOT NULL DEFAULT 'General' AFTER title,
 ADD COLUMN category VARCHAR(120) NOT NULL DEFAULT 'Mock Test' AFTER exam_name,
 ADD INDEX idx_tests_catalogue(exam_name,category,subject_id);

CREATE TABLE remember_tokens (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 role ENUM('admin','student') NOT NULL,
 selector CHAR(24) NOT NULL UNIQUE,
 validator_hash CHAR(64) NOT NULL,
 expires_at DATETIME NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_remember_user(user_id,role),
 INDEX idx_remember_expiry(expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
