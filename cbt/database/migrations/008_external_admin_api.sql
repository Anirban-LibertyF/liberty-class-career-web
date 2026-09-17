ALTER TABLE tests ADD COLUMN external_id VARCHAR(100) NULL AFTER id;
CREATE UNIQUE INDEX uq_tests_external_id ON tests(external_id);
