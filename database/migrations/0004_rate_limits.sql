-- SaaS Seeder Template - HTTP rate limiting
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_rate_limit_hits` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `bucket_key` CHAR(64) NOT NULL,
  `hit_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rate_limit_bucket_time` (`bucket_key`, `hit_at`),
  KEY `idx_rate_limit_hit_at` (`hit_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
