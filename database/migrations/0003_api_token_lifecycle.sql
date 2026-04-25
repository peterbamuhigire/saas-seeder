-- April world-class Phase 04: API token lifecycle
-- Access tokens are JWTs keyed by jti in tbl_user_sessions.
-- Refresh tokens are opaque secrets stored only as HMAC-SHA256 hashes.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `tbl_user_sessions`
  ADD COLUMN `jti` CHAR(32) NULL AFTER `token`,
  ADD COLUMN `token_hash` CHAR(64) NULL AFTER `jti`;

UPDATE `tbl_user_sessions`
SET `jti` = JSON_UNQUOTE(JSON_EXTRACT(`session_data`, '$.jti'))
WHERE `jti` IS NULL
  AND `session_data` IS NOT NULL
  AND JSON_VALID(`session_data`)
  AND JSON_EXTRACT(`session_data`, '$.jti') IS NOT NULL;

UPDATE `tbl_user_sessions`
SET `jti` = JSON_UNQUOTE(JSON_EXTRACT(`session_data`, '$.session_id'))
WHERE `jti` IS NULL
  AND `session_data` IS NOT NULL
  AND JSON_VALID(`session_data`)
  AND JSON_EXTRACT(`session_data`, '$.session_id') IS NOT NULL;

UPDATE `tbl_user_sessions`
SET `jti` = `token`
WHERE `jti` IS NULL
  AND `token` IS NOT NULL
  AND CHAR_LENGTH(`token`) = 32;

UPDATE `tbl_user_sessions`
SET `token_hash` = SHA2(`token`, 256)
WHERE `token_hash` IS NULL
  AND `token` IS NOT NULL;

ALTER TABLE `tbl_user_sessions`
  MODIFY COLUMN `token` VARCHAR(255) NULL,
  MODIFY COLUMN `ip_address` VARCHAR(45) NULL,
  MODIFY COLUMN `user_agent` VARCHAR(255) NULL;

ALTER TABLE `tbl_user_sessions`
  DROP INDEX `uk_token`,
  ADD UNIQUE KEY `uk_session_jti` (`jti`),
  ADD KEY `idx_session_token_hash` (`token_hash`);

CREATE TABLE IF NOT EXISTS `tbl_refresh_tokens` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `franchise_id` BIGINT UNSIGNED NULL,
  `token_hash` CHAR(64) NOT NULL,
  `family_id` CHAR(32) NOT NULL,
  `device_id` VARCHAR(128) NULL,
  `user_agent_hash` CHAR(64) NULL,
  `ip_address` VARCHAR(45) NULL,
  `expires_at` DATETIME NOT NULL,
  `revoked_at` DATETIME NULL,
  `replaced_by_token_id` BIGINT UNSIGNED NULL,
  `reuse_detected_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_refresh_token_hash` (`token_hash`),
  KEY `idx_refresh_user` (`user_id`, `expires_at`),
  KEY `idx_refresh_family` (`family_id`),
  KEY `idx_refresh_device` (`device_id`),
  KEY `idx_refresh_replaced_by` (`replaced_by_token_id`),
  CONSTRAINT `fk_refresh_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_refresh_franchise` FOREIGN KEY (`franchise_id`) REFERENCES `tbl_franchises` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_refresh_replaced_by` FOREIGN KEY (`replaced_by_token_id`) REFERENCES `tbl_refresh_tokens` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_create_user_session`$$
CREATE PROCEDURE `sp_create_user_session`(
  IN p_user_id BIGINT UNSIGNED,
  IN p_franchise_id BIGINT UNSIGNED,
  IN p_jti VARCHAR(255),
  IN p_ip_address VARCHAR(45),
  IN p_user_agent VARCHAR(255),
  IN p_expires_at TIMESTAMP,
  IN p_remember_me TINYINT(1),
  IN p_session_data JSON
)
BEGIN
  INSERT INTO tbl_user_sessions (
    franchise_id, user_id, token, jti, token_hash, remember_me, ip_address, user_agent, expires_at, session_data
  ) VALUES (
    p_franchise_id, p_user_id, NULL, p_jti, SHA2(p_jti, 256), p_remember_me, p_ip_address, p_user_agent, p_expires_at, p_session_data
  );
END$$

DROP PROCEDURE IF EXISTS `sp_validate_session`$$
CREATE PROCEDURE `sp_validate_session`(
  IN p_jti VARCHAR(255)
)
BEGIN
  SELECT
    CASE
      WHEN invalidated_at IS NOT NULL THEN 0
      WHEN expires_at <= NOW() THEN 0
      ELSE 1
    END AS is_valid
  FROM tbl_user_sessions
  WHERE jti = p_jti
  LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS `sp_invalidate_session`$$
CREATE PROCEDURE `sp_invalidate_session`(
  IN p_jti VARCHAR(255)
)
BEGIN
  UPDATE tbl_user_sessions
  SET invalidated_at = NOW()
  WHERE jti = p_jti;
END$$

DROP PROCEDURE IF EXISTS `sp_invalidate_user_sessions`$$
CREATE PROCEDURE `sp_invalidate_user_sessions`(
  IN p_user_id BIGINT UNSIGNED,
  IN p_franchise_id BIGINT UNSIGNED
)
BEGIN
  UPDATE tbl_user_sessions
  SET invalidated_at = NOW()
  WHERE user_id = p_user_id
    AND (p_franchise_id IS NULL OR franchise_id = p_franchise_id)
    AND invalidated_at IS NULL;
END$$

DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;
