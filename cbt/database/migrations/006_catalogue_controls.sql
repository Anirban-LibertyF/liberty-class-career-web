ALTER TABLE subjects ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER slug;

CREATE TABLE exam_catalogue (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL UNIQUE,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO exam_catalogue(name) VALUES
 ('NEET'),('JEE Main'),('JEE Advanced'),('WBJEE'),('JENPAS UG'),
 ('Nursing Entrance'),('Railway'),('Banking'),('SSC'),('UPSC'),('WBCS'),('Defence');

INSERT IGNORE INTO exam_catalogue(name)
SELECT DISTINCT exam_name FROM tests
WHERE deleted_at IS NULL AND exam_name IS NOT NULL AND TRIM(exam_name)<>'';
