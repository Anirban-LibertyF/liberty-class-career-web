ALTER TABLE tests ADD COLUMN unique_key CHAR(8) NULL AFTER id;
UPDATE tests SET unique_key=UPPER(SUBSTRING(REPLACE(UUID(),'-',''),1,8)) WHERE unique_key IS NULL;
ALTER TABLE tests MODIFY unique_key CHAR(8) NOT NULL, ADD UNIQUE KEY uq_tests_unique_key(unique_key);
ALTER TABLE questions ADD COLUMN unique_key CHAR(8) NULL AFTER id;
UPDATE questions SET unique_key=UPPER(SUBSTRING(REPLACE(UUID(),'-',''),1,8)) WHERE unique_key IS NULL;
ALTER TABLE questions MODIFY unique_key CHAR(8) NOT NULL, ADD UNIQUE KEY uq_questions_unique_key(unique_key);
