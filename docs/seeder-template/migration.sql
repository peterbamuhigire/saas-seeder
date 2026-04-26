-- SaaS Seeder Template — Auth & RBAC Migration
-- Version: 2.0 (2026-03-29 — Standards Compliance Rewrite)
-- Collation: utf8mb4_unicode_ci on all tables
-- Row format: DYNAMIC on all tables
-- Foreign keys: All relationships constrained

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `tbl_schema_migrations` (
  `migration_id` VARCHAR(150) NOT NULL,
  `checksum` CHAR(64) NOT NULL,
  `applied_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `applied_by` VARCHAR(150) NULL,
  `execution_ms` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`migration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ================================================================
-- 1. FRANCHISES (must exist before tbl_users for FK)
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_franchises` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) NOT NULL COMMENT 'Unique slug',
  `business_name` VARCHAR(150) NULL,
  `business_type` ENUM('sole_proprietor','partnership','corporation','llc','non_profit','other') DEFAULT 'corporation',
  `email` VARCHAR(100) NULL,
  `phone` VARCHAR(20) NULL,
  `website` VARCHAR(255) NULL,
  `address_line1` VARCHAR(255) NULL,
  `address_line2` VARCHAR(255) NULL,
  `city` VARCHAR(100) NULL,
  `state_province` VARCHAR(100) NULL,
  `postal_code` VARCHAR(20) NULL,
  `country` VARCHAR(2) DEFAULT 'UG' COMMENT 'ISO 3166-1 alpha-2',
  `tax_id` VARCHAR(50) NULL,
  `timezone` VARCHAR(50) DEFAULT 'Africa/Kampala',
  `currency` VARCHAR(3) DEFAULT 'UGX' COMMENT 'ISO 4217',
  `language` VARCHAR(5) DEFAULT 'en' COMMENT 'ISO 639-1',
  `subscription_plan` VARCHAR(50) NULL,
  `subscription_status` ENUM('trial','active','suspended','cancelled','expired') DEFAULT 'trial',
  `trial_ends_at` DATETIME NULL,
  `max_users` INT UNSIGNED DEFAULT 10,
  `max_storage_mb` INT UNSIGNED DEFAULT 1024,
  `logo_url` VARCHAR(500) NULL,
  `enabled_features` JSON NULL,
  `custom_settings` JSON NULL,
  `permission_version` INT UNSIGNED DEFAULT 1 COMMENT 'Increment to invalidate cached permissions',
  `status` ENUM('active','inactive','suspended','pending_approval','deleted') DEFAULT 'pending_approval',
  `onboarding_completed` TINYINT(1) DEFAULT 0,
  `owner_user_id` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_franchise_code` (`code`),
  KEY `idx_status` (`status`),
  KEY `idx_owner` (`owner_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ================================================================
-- 2. PERMISSIONS
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `module` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_permission_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ================================================================
-- 3. GLOBAL ROLES
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_global_roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_global_role_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ================================================================
-- 4. USERS
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_id` BIGINT UNSIGNED DEFAULT NULL,
  `username` VARCHAR(50) NOT NULL,
  `user_type` ENUM('super_admin','owner','distributor','staff') NOT NULL DEFAULT 'staff',
  `email` VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `photo_path` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active','inactive','locked','pending','invited','suspended') NOT NULL DEFAULT 'pending',
  `last_login` DATETIME DEFAULT NULL,
  `password_reset_token` VARCHAR(100) DEFAULT NULL,
  `password_reset_expires` DATETIME DEFAULT NULL,
  `force_password_change` TINYINT(1) NOT NULL DEFAULT 1,
  `failed_login_attempts` SMALLINT NOT NULL DEFAULT 0,
  `locked_until` DATETIME DEFAULT NULL COMMENT 'Account lockout expiry',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_root` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  UNIQUE KEY `uk_email` (`franchise_id`, `email`),
  KEY `idx_status` (`status`),
  KEY `idx_franchise` (`franchise_id`),
  CONSTRAINT `fk_users_franchise` FOREIGN KEY (`franchise_id`) REFERENCES `tbl_franchises` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Add FK from franchises.owner_user_id -> users.id (circular, added after both exist)
ALTER TABLE `tbl_franchises` ADD CONSTRAINT `fk_franchise_owner` FOREIGN KEY (`owner_user_id`) REFERENCES `tbl_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ================================================================
-- 5. GLOBAL ROLE PERMISSIONS (junction)
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_global_role_permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `global_role_id` BIGINT UNSIGNED NOT NULL,
  `permission_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_global_role_permission` (`global_role_id`, `permission_id`),
  CONSTRAINT `fk_grp_role` FOREIGN KEY (`global_role_id`) REFERENCES `tbl_global_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_grp_permission` FOREIGN KEY (`permission_id`) REFERENCES `tbl_permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ================================================================
-- 6. FRANCHISE ROLE OVERRIDES
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_franchise_role_overrides` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_id` BIGINT UNSIGNED NOT NULL,
  `global_role_id` BIGINT UNSIGNED NOT NULL,
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = disabled for this franchise',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_franchise_role_perm` (`franchise_id`, `global_role_id`, `permission_id`),
  CONSTRAINT `fk_fro_franchise` FOREIGN KEY (`franchise_id`) REFERENCES `tbl_franchises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_fro_role` FOREIGN KEY (`global_role_id`) REFERENCES `tbl_global_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_fro_permission` FOREIGN KEY (`permission_id`) REFERENCES `tbl_permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ================================================================
-- 7. USER ROLES (junction)
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_user_roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `global_role_id` BIGINT UNSIGNED NOT NULL,
  `assigned_by` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_franchise_user_global_role` (`franchise_id`, `user_id`, `global_role_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_ur_franchise` FOREIGN KEY (`franchise_id`) REFERENCES `tbl_franchises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ur_role` FOREIGN KEY (`global_role_id`) REFERENCES `tbl_global_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ================================================================
-- 8. USER PERMISSION OVERRIDES
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_user_permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `allowed` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = grant, 0 = deny',
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_perm` (`franchise_id`, `user_id`, `permission_id`),
  KEY `idx_franchise_user` (`franchise_id`, `user_id`),
  CONSTRAINT `fk_up_franchise` FOREIGN KEY (`franchise_id`) REFERENCES `tbl_franchises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_up_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_up_permission` FOREIGN KEY (`permission_id`) REFERENCES `tbl_permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ================================================================
-- 9. USER SESSIONS
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_user_sessions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_id` BIGINT UNSIGNED DEFAULT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `token` VARCHAR(255) NULL,
  `jti` CHAR(32) NULL,
  `token_hash` CHAR(64) NULL,
  `remember_me` TINYINT(1) NOT NULL DEFAULT 0,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `expires_at` TIMESTAMP NOT NULL,
  `invalidated_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `session_data` JSON DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_session_jti` (`jti`),
  KEY `idx_session_token_hash` (`token_hash`),
  KEY `idx_user_sessions` (`user_id`, `expires_at`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sessions_franchise` FOREIGN KEY (`franchise_id`) REFERENCES `tbl_franchises` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ================================================================
-- 10. REFRESH TOKENS
-- ================================================================
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

-- ================================================================
-- 11. LOGIN ATTEMPTS
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_login_attempts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) NOT NULL,
  `attempt_time` TIMESTAMP NOT NULL,
  `success` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_attempt_time` (`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ================================================================
-- 11. AUDIT LOG (immutable — no UPDATE/DELETE by application)
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_audit_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `franchise_id` BIGINT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL COMMENT 'e.g. USER_CREATED, PERMISSION_CHANGED',
  `entity_type` VARCHAR(50) DEFAULT NULL COMMENT 'e.g. user, role, franchise',
  `entity_id` BIGINT UNSIGNED DEFAULT NULL,
  `details` JSON DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_franchise` (`franchise_id`),
  KEY `idx_audit_action` (`action`),
  KEY `idx_audit_entity` (`entity_type`, `entity_id`),
  KEY `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ================================================================
-- STORED PROCEDURES
-- ================================================================
DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_authenticate_user`$$
CREATE PROCEDURE `sp_authenticate_user`(
  IN p_username VARCHAR(50),
  IN p_franchise_id BIGINT UNSIGNED,
  OUT p_user_id BIGINT UNSIGNED,
  OUT p_status VARCHAR(50),
  OUT p_password_hash VARCHAR(255)
)
BEGIN
  DECLARE v_user_id BIGINT UNSIGNED DEFAULT NULL;
  DECLARE v_status VARCHAR(50) DEFAULT NULL;
  DECLARE v_hash VARCHAR(255) DEFAULT NULL;
  DECLARE v_locked_until DATETIME DEFAULT NULL;
  DECLARE v_failed_attempts SMALLINT DEFAULT 0;

  IF p_franchise_id IS NULL THEN
    SELECT id, status, password_hash, locked_until, failed_login_attempts
      INTO v_user_id, v_status, v_hash, v_locked_until, v_failed_attempts
    FROM tbl_users
    WHERE (username = p_username OR email = p_username)
      AND franchise_id IS NULL
    LIMIT 1;
  ELSE
    SELECT id, status, password_hash, locked_until, failed_login_attempts
      INTO v_user_id, v_status, v_hash, v_locked_until, v_failed_attempts
    FROM tbl_users
    WHERE (username = p_username OR email = p_username)
      AND franchise_id = p_franchise_id
    LIMIT 1;
  END IF;

  IF v_user_id IS NULL THEN
    SET p_user_id = 0;
    SET p_status = 'USER_NOT_FOUND';
    SET p_password_hash = NULL;
  ELSEIF v_locked_until IS NOT NULL AND v_locked_until > NOW() THEN
    SET p_user_id = v_user_id;
    SET p_status = 'ACCOUNT_LOCKED';
    SET p_password_hash = NULL;
  ELSEIF v_status <> 'active' THEN
    SET p_user_id = v_user_id;
    SET p_status = 'ACCOUNT_INACTIVE';
    SET p_password_hash = v_hash;
  ELSE
    -- Reset lock if expired
    IF v_locked_until IS NOT NULL AND v_locked_until <= NOW() THEN
      UPDATE tbl_users SET locked_until = NULL, failed_login_attempts = 0 WHERE id = v_user_id;
    END IF;
    SET p_user_id = v_user_id;
    SET p_status = 'SUCCESS';
    SET p_password_hash = v_hash;
  END IF;
END$$

DROP PROCEDURE IF EXISTS `sp_get_user_data`$$
CREATE PROCEDURE `sp_get_user_data`(
  IN p_user_id BIGINT UNSIGNED,
  OUT p_status VARCHAR(50)
)
BEGIN
  SELECT
    u.id,
    u.franchise_id,
    u.username,
    u.email,
    u.first_name,
    u.last_name,
    u.phone,
    u.user_type,
    u.force_password_change,
    GROUP_CONCAT(DISTINCT gr.name) AS roles,
    GROUP_CONCAT(DISTINCT p.code) AS permissions,
    COALESCE(f.name, '') AS franchise_name,
    COALESCE(f.currency, '') AS currency,
    COALESCE(f.code, '') AS franchise_code,
    COALESCE(f.country, '') AS country,
    COALESCE(f.language, 'en') AS language,
    COALESCE(f.timezone, 'Africa/Kampala') AS timezone
  FROM tbl_users u
  LEFT JOIN tbl_franchises f ON f.id = u.franchise_id
  LEFT JOIN tbl_user_roles ur ON ur.user_id = u.id
  LEFT JOIN tbl_global_roles gr ON gr.id = ur.global_role_id
  LEFT JOIN tbl_global_role_permissions grp ON grp.global_role_id = gr.id
  LEFT JOIN tbl_permissions p ON p.id = grp.permission_id
  WHERE u.id = p_user_id
  GROUP BY u.id;

  SET p_status = 'Success';
END$$

DROP PROCEDURE IF EXISTS `sp_get_user_permissions`$$
CREATE PROCEDURE `sp_get_user_permissions`(
  IN p_user_id BIGINT UNSIGNED,
  IN p_franchise_id BIGINT UNSIGNED
)
BEGIN
  DECLARE v_user_type VARCHAR(50);

  SELECT user_type INTO v_user_type
  FROM tbl_users WHERE id = p_user_id LIMIT 1;

  -- Super admins get all permissions
  IF v_user_type = 'super_admin' THEN
    SELECT GROUP_CONCAT(code ORDER BY code SEPARATOR ',') AS permissions
    FROM tbl_permissions;
  ELSE
    -- Build effective permissions:
    -- 1. Start with global role permissions
    -- 2. Remove franchise-level disabled overrides
    -- 3. Apply user-level overrides (grant or deny)
    SELECT GROUP_CONCAT(DISTINCT final_perms.code ORDER BY final_perms.code SEPARATOR ',') AS permissions
    FROM (
      -- Role permissions minus franchise overrides
      SELECT p.code
      FROM tbl_user_roles ur
      JOIN tbl_global_role_permissions grp ON grp.global_role_id = ur.global_role_id
      JOIN tbl_permissions p ON p.id = grp.permission_id
      LEFT JOIN tbl_franchise_role_overrides fro
        ON fro.franchise_id = ur.franchise_id
        AND fro.global_role_id = ur.global_role_id
        AND fro.permission_id = grp.permission_id
      WHERE ur.user_id = p_user_id
        AND (ur.franchise_id = p_franchise_id OR p_franchise_id IS NULL)
        AND (fro.id IS NULL OR fro.is_enabled = 1)

      UNION

      -- User-level explicit grants
      SELECT p.code
      FROM tbl_user_permissions up
      JOIN tbl_permissions p ON p.id = up.permission_id
      WHERE up.user_id = p_user_id
        AND up.franchise_id = p_franchise_id
        AND up.allowed = 1
    ) AS granted_perms

    -- Subtract user-level explicit denials
    LEFT JOIN (
      SELECT p.code
      FROM tbl_user_permissions up
      JOIN tbl_permissions p ON p.id = up.permission_id
      WHERE up.user_id = p_user_id
        AND up.franchise_id = p_franchise_id
        AND up.allowed = 0
    ) AS denied_perms ON denied_perms.code = granted_perms.code

    -- Only include this in the final result alias
    WHERE denied_perms.code IS NULL

    -- Alias for the outer SELECT to reference
    ;

    -- Re-wrap: the above is a single SELECT returning permissions column
  END IF;
END$$

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

DROP PROCEDURE IF EXISTS `sp_log_failed_login`$$
CREATE PROCEDURE `sp_log_failed_login`(
  IN p_username VARCHAR(50),
  IN p_ip_address VARCHAR(45),
  IN p_user_agent VARCHAR(255),
  IN p_attempt_time TIMESTAMP
)
BEGIN
  INSERT INTO tbl_login_attempts (username, ip_address, user_agent, attempt_time, success)
  VALUES (p_username, p_ip_address, p_user_agent, p_attempt_time, 0);
END$$

DROP PROCEDURE IF EXISTS `sp_increment_failed_attempts`$$
CREATE PROCEDURE `sp_increment_failed_attempts`(
  IN p_user_id BIGINT UNSIGNED
)
BEGIN
  DECLARE v_attempts SMALLINT;

  UPDATE tbl_users
  SET failed_login_attempts = failed_login_attempts + 1
  WHERE id = p_user_id;

  -- Auto-lock after 5 failures
  SELECT failed_login_attempts INTO v_attempts
  FROM tbl_users WHERE id = p_user_id;

  IF v_attempts >= 5 THEN
    UPDATE tbl_users
    SET locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
    WHERE id = p_user_id;
  END IF;
END$$

DROP PROCEDURE IF EXISTS `sp_reset_failed_attempts`$$
CREATE PROCEDURE `sp_reset_failed_attempts`(
  IN p_user_id BIGINT UNSIGNED
)
BEGIN
  UPDATE tbl_users
  SET failed_login_attempts = 0, locked_until = NULL
  WHERE id = p_user_id;
END$$

DELIMITER ;

-- ================================================================
-- 12. SYSTEM SETTINGS (global key-value config)
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_system_settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NULL,
  `setting_type` ENUM('string','integer','boolean','json') DEFAULT 'string',
  `description` VARCHAR(255) NULL,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ================================================================
-- 13. FRANCHISE SETTINGS (per-tenant key-value config)
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_franchise_settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_id` BIGINT UNSIGNED NOT NULL,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NULL,
  `setting_type` ENUM('string','integer','boolean','json') DEFAULT 'string',
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_franchise_setting` (`franchise_id`, `setting_key`),
  CONSTRAINT `fk_fs_franchise` FOREIGN KEY (`franchise_id`) REFERENCES `tbl_franchises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ================================================================
-- 14. NOTIFICATIONS (in-app notifications)
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_id` BIGINT UNSIGNED DEFAULT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Recipient',
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info','success','warning','error') DEFAULT 'info',
  `link` VARCHAR(500) NULL COMMENT 'URL to navigate to on click',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`, `is_read`),
  KEY `idx_notif_franchise` (`franchise_id`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notif_franchise` FOREIGN KEY (`franchise_id`) REFERENCES `tbl_franchises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ================================================================
-- 15. FILE UPLOADS (metadata for uploaded files)
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_file_uploads` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_id` BIGINT UNSIGNED DEFAULT NULL,
  `uploaded_by` BIGINT UNSIGNED NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `stored_name` VARCHAR(255) NOT NULL COMMENT 'UUID-based filename on disk',
  `file_path` VARCHAR(500) NOT NULL COMMENT 'Relative path from public/',
  `mime_type` VARCHAR(100) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL COMMENT 'Bytes',
  `entity_type` VARCHAR(50) NULL COMMENT 'e.g. user_photo, document, receipt',
  `entity_id` BIGINT UNSIGNED NULL COMMENT 'FK to the owning record',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_upload_entity` (`entity_type`, `entity_id`),
  KEY `idx_upload_franchise` (`franchise_id`),
  CONSTRAINT `fk_upload_user` FOREIGN KEY (`uploaded_by`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_upload_franchise` FOREIGN KEY (`franchise_id`) REFERENCES `tbl_franchises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ================================================================
-- 16. API SIGNUP REQUESTS (franchise onboarding queue)
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_api_signup_requests` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) NULL,
  `franchise_name` VARCHAR(255) NOT NULL,
  `plan_code` VARCHAR(100) NOT NULL DEFAULT 'trial',
  `language` VARCHAR(10) DEFAULT 'en',
  `country` VARCHAR(80) NULL,
  `currency` VARCHAR(10) NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `verify_token` VARCHAR(64) NOT NULL,
  `verify_token_expires_at` DATETIME NOT NULL,
  `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `verified_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_signup_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- ================================================================
-- SEED DATA
-- ================================================================
CREATE TABLE IF NOT EXISTS `tbl_modules` (
  `code` VARCHAR(50) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `version` VARCHAR(50) NOT NULL DEFAULT '1.0.0',
  `is_core` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','disabled','deprecated') NOT NULL DEFAULT 'active',
  `config` JSON NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `tbl_franchise_modules` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_id` BIGINT UNSIGNED NOT NULL,
  `module_code` VARCHAR(50) NOT NULL,
  `status` ENUM('enabled','disabled') NOT NULL DEFAULT 'enabled',
  `config` JSON NULL,
  `enabled_at` DATETIME NULL,
  `disabled_at` DATETIME NULL,
  `enabled_by` BIGINT UNSIGNED NULL,
  `disabled_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_franchise_module` (`franchise_id`, `module_code`),
  KEY `idx_module_code` (`module_code`),
  CONSTRAINT `fk_fm_franchise` FOREIGN KEY (`franchise_id`) REFERENCES `tbl_franchises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_fm_module` FOREIGN KEY (`module_code`) REFERENCES `tbl_modules` (`code`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_fm_enabled_by` FOREIGN KEY (`enabled_by`) REFERENCES `tbl_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_fm_disabled_by` FOREIGN KEY (`disabled_by`) REFERENCES `tbl_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `tbl_module_dependencies` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `module_code` VARCHAR(50) NOT NULL,
  `depends_on_module_code` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_module_dependency` (`module_code`, `depends_on_module_code`),
  CONSTRAINT `fk_md_module` FOREIGN KEY (`module_code`) REFERENCES `tbl_modules` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_md_depends_on` FOREIGN KEY (`depends_on_module_code`) REFERENCES `tbl_modules` (`code`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `tbl_rate_limit_hits` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `bucket_key` CHAR(64) NOT NULL,
  `hit_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rate_limit_bucket_time` (`bucket_key`, `hit_at`),
  KEY `idx_rate_limit_hit_at` (`hit_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `tbl_modules` (`code`, `name`, `version`, `is_core`, `status`) VALUES
('AUTH', 'Authentication', '1.0.0', 1, 'active'),
('RBAC', 'Roles and permissions', '1.0.0', 1, 'active'),
('TENANT', 'Tenant management', '1.0.0', 1, 'active'),
('DASHBOARD', 'Dashboard', '1.0.0', 1, 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `version` = VALUES(`version`), `is_core` = VALUES(`is_core`), `status` = VALUES(`status`);

INSERT INTO `tbl_module_dependencies` (`module_code`, `depends_on_module_code`) VALUES
('RBAC', 'AUTH'),
('TENANT', 'AUTH'),
('DASHBOARD', 'AUTH')
ON DUPLICATE KEY UPDATE `depends_on_module_code` = VALUES(`depends_on_module_code`);

INSERT INTO tbl_permissions (name, code, module, description) VALUES
-- Dashboard
('View Dashboard', 'VIEW_DASHBOARD', 'DASHBOARD', 'Access main dashboard'),
-- User Management
('View Users', 'VIEW_USERS', 'USERS', 'View user list'),
('Create Users', 'CREATE_USER', 'USERS', 'Create new users'),
('Edit Users', 'EDIT_USER', 'USERS', 'Edit existing users'),
('Delete Users', 'DELETE_USER', 'USERS', 'Deactivate/delete users'),
('Manage Users', 'MANAGE_USERS', 'USERS', 'Full user management access'),
-- Role & Permission Management
('View Roles', 'VIEW_ROLES', 'ROLES', 'View roles list'),
('Manage Roles', 'MANAGE_ROLES', 'ROLES', 'Create/edit/delete roles and assign permissions'),
-- System Administration
('View Audit Logs', 'VIEW_AUDIT_LOGS', 'ADMIN', 'Access audit logs'),
('Manage Settings', 'MANAGE_SETTINGS', 'ADMIN', 'System and franchise configuration'),
('Manage Franchises', 'MANAGE_FRANCHISES', 'ADMIN', 'Create/edit/suspend franchises'),
-- Notifications
('View Notifications', 'VIEW_NOTIFICATIONS', 'NOTIFICATIONS', 'View own notifications'),
('Send Notifications', 'SEND_NOTIFICATIONS', 'NOTIFICATIONS', 'Send notifications to users'),
-- File Management
('Upload Files', 'UPLOAD_FILES', 'FILES', 'Upload files and documents'),
('Delete Files', 'DELETE_FILES', 'FILES', 'Delete uploaded files');

INSERT INTO tbl_global_roles (code, name, description, is_system)
VALUES ('SUPER_ADMIN', 'Super Admin', 'Full system access', 1);

-- Default system settings
INSERT INTO tbl_system_settings (setting_key, setting_value, setting_type, description) VALUES
('app_name', 'SaaS Seeder', 'string', 'Application display name'),
('app_timezone', 'Africa/Kampala', 'string', 'Default timezone for new franchises'),
('app_currency', 'UGX', 'string', 'Default currency for new franchises'),
('app_language', 'en', 'string', 'Default language for new franchises'),
('app_country', 'UG', 'string', 'Default country code (ISO 3166-1 alpha-2)'),
('max_login_attempts', '5', 'integer', 'Failed login attempts before lockout'),
('lockout_duration_minutes', '15', 'integer', 'Account lockout duration in minutes'),
('session_timeout_seconds', '1800', 'integer', 'Session inactivity timeout'),
('allow_self_registration', '0', 'boolean', 'Allow public franchise signup'),
('require_email_verification', '1', 'boolean', 'Require email verification on signup'),
('max_upload_size_mb', '10', 'integer', 'Maximum file upload size in MB'),
('allowed_upload_types', '["jpg","jpeg","png","webp","pdf","doc","docx","xls","xlsx"]', 'json', 'Allowed file upload extensions');

-- NOTE: No default user is seeded. Use super-user-dev.php to create the
-- initial super_admin account with a properly hashed (Argon2ID) password.

SET FOREIGN_KEY_CHECKS = 1;
