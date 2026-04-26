-- Returns rows for missing required schema objects.
SELECT 'missing table: tbl_schema_migrations' AS issue
WHERE NOT EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_schema_migrations')
UNION ALL SELECT 'missing table: tbl_modules'
WHERE NOT EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_modules')
UNION ALL SELECT 'missing table: tbl_franchise_modules'
WHERE NOT EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_franchise_modules')
UNION ALL SELECT 'missing table: tbl_refresh_tokens'
WHERE NOT EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_refresh_tokens')
UNION ALL SELECT 'missing table: tbl_rate_limit_hits'
WHERE NOT EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_rate_limit_hits')
UNION ALL SELECT CONCAT('non-utf8mb4 table: ', TABLE_NAME)
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_COLLATION NOT LIKE 'utf8mb4%';
