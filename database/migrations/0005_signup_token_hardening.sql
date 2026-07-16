-- Harden signup verification secrets at rest.

SET NAMES utf8mb4;

DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_migrate_0005_signup_token`$$
CREATE PROCEDURE `sp_migrate_0005_signup_token`()
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_api_signup_requests'
      AND COLUMN_NAME = 'verify_token_hash'
  ) THEN
    ALTER TABLE `tbl_api_signup_requests`
      ADD COLUMN `verify_token_hash` CHAR(64) NULL AFTER `password_hash`;
  END IF;

  IF EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_api_signup_requests'
      AND COLUMN_NAME = 'verify_token'
  ) THEN
    UPDATE `tbl_api_signup_requests`
    SET `verify_token_hash` = SHA2(`verify_token`, 256)
    WHERE `verify_token_hash` IS NULL;

    ALTER TABLE `tbl_api_signup_requests` DROP COLUMN `verify_token`;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_api_signup_requests'
      AND INDEX_NAME = 'uk_signup_verify_token_hash'
  ) THEN
    ALTER TABLE `tbl_api_signup_requests`
      ADD UNIQUE KEY `uk_signup_verify_token_hash` (`verify_token_hash`);
  END IF;
END$$

CALL `sp_migrate_0005_signup_token`()$$
DROP PROCEDURE `sp_migrate_0005_signup_token`$$

DELIMITER ;
