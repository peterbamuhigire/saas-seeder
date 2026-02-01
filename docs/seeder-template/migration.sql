-- Seeder Template Auth and RBAC Migration
-- Baseline: Maduuka conventions
-- Purpose: Create minimal auth + RBAC schema and login procedures

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `tbl_users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_id` BIGINT UNSIGNED DEFAULT NULL,
  `distributor_id` BIGINT UNSIGNED DEFAULT NULL,
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
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_root` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  UNIQUE KEY `uk_email` (`franchise_id`,`email`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `module` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_permission_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_global_role_permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `global_role_id` BIGINT UNSIGNED NOT NULL,
  `permission_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_global_role_permission` (`global_role_id`,`permission_id`),
  KEY `idx_global_role` (`global_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `is_system_role` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_role_name` (`franchise_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_role_permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
  `role_id` BIGINT UNSIGNED DEFAULT NULL,
  `global_role_id` BIGINT UNSIGNED DEFAULT NULL,
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_role_perm_local` (`franchise_id`,`role_id`,`permission_id`),
  UNIQUE KEY `uk_role_perm_global` (`franchise_id`,`global_role_id`,`permission_id`),
  KEY `idx_global_role` (`global_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_user_roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `global_role_id` BIGINT UNSIGNED NOT NULL,
  `assigned_by` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_franchise_user_global_role` (`franchise_id`,`user_id`,`global_role_id`),
  KEY `idx_franchise` (`franchise_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_user_global_role` (`global_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_user_permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `allowed` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_perm` (`franchise_id`,`user_id`,`permission_id`),
  KEY `idx_franchise_user` (`franchise_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_user_sessions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `franchise_id` BIGINT DEFAULT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `remember_me` TINYINT(1) NOT NULL DEFAULT 0,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) NOT NULL,
  `expires_at` TIMESTAMP NOT NULL,
  `invalidated_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `session_data` JSON DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token` (`token`),
  KEY `idx_user_sessions` (`user_id`,`expires_at`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

  IF p_franchise_id IS NULL THEN
    SELECT id, status, password_hash INTO v_user_id, v_status, v_hash
    FROM tbl_users
    WHERE (username = p_username OR email = p_username)
      AND franchise_id IS NULL
    LIMIT 1;
  ELSE
    SELECT id, status, password_hash INTO v_user_id, v_status, v_hash
    FROM tbl_users
    WHERE (username = p_username OR email = p_username)
      AND franchise_id = p_franchise_id
    LIMIT 1;
  END IF;

  IF v_user_id IS NULL THEN
    SET p_user_id = 0;
    SET p_status = 'USER_NOT_FOUND';
    SET p_password_hash = NULL;
  ELSEIF v_status <> 'active' THEN
    SET p_user_id = v_user_id;
    SET p_status = 'ACCOUNT_INACTIVE';
    SET p_password_hash = v_hash;
  ELSE
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
    '' AS franchise_name,
    '' AS currency,
    '' AS franchise_code,
    '' AS country,
    'en' AS language
  FROM tbl_users u
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
  FROM tbl_users
  WHERE id = p_user_id
  LIMIT 1;

  IF v_user_type = 'super_admin' THEN
    SELECT GROUP_CONCAT(code ORDER BY code SEPARATOR ',') AS permissions
    FROM tbl_permissions;
  ELSE
    SELECT GROUP_CONCAT(DISTINCT p.code ORDER BY p.code SEPARATOR ',') AS permissions
    FROM tbl_user_roles ur
    JOIN tbl_global_role_permissions grp ON grp.global_role_id = ur.global_role_id
    JOIN tbl_permissions p ON p.id = grp.permission_id
    WHERE ur.user_id = p_user_id;
  END IF;
END$$

DROP PROCEDURE IF EXISTS `sp_create_user_session`$$
CREATE PROCEDURE `sp_create_user_session`(
  IN p_user_id BIGINT UNSIGNED,
  IN p_franchise_id BIGINT,
  IN p_token VARCHAR(255),
  IN p_ip_address VARCHAR(45),
  IN p_user_agent VARCHAR(255),
  IN p_expires_at TIMESTAMP,
  IN p_remember_me TINYINT(1),
  IN p_session_data JSON
)
BEGIN
  INSERT INTO tbl_user_sessions (
    franchise_id, user_id, token, remember_me, ip_address, user_agent, expires_at, session_data
  ) VALUES (
    p_franchise_id, p_user_id, p_token, p_remember_me, p_ip_address, p_user_agent, p_expires_at, p_session_data
  );
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
  UPDATE tbl_users
  SET failed_login_attempts = failed_login_attempts + 1
  WHERE id = p_user_id;
END$$

DROP PROCEDURE IF EXISTS `sp_reset_failed_attempts`$$
CREATE PROCEDURE `sp_reset_failed_attempts`(
  IN p_user_id BIGINT UNSIGNED
)
BEGIN
  UPDATE tbl_users
  SET failed_login_attempts = 0
  WHERE id = p_user_id;
END$$

DELIMITER ;

INSERT INTO tbl_permissions (name, code, module, description) VALUES
('View Dashboard', 'VIEW_DASHBOARD', 'DASHBOARD', 'Access main dashboard'),
('Manage Users', 'MANAGE_USERS', 'ADMIN', 'Create/edit users'),
('Manage Roles', 'MANAGE_ROLES', 'ADMIN', 'Manage roles and permissions'),
('View Audit Logs', 'VIEW_AUDIT_LOGS', 'ADMIN', 'Access audit logs'),
('Manage Settings', 'MANAGE_SETTINGS', 'ADMIN', 'System configuration');

INSERT INTO tbl_global_roles (code, name, description, is_system)
VALUES ('SUPER_ADMIN', 'Super Admin', 'Full system access', 1);

INSERT INTO tbl_users (
  franchise_id,
  username,
  user_type,
  email,
  password_hash,
  first_name,
  last_name,
  phone,
  status,
  is_root,
  force_password_change
) VALUES (
  NULL,
  'root',
  'super_admin',
  'peter@techguypeter.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'Peter',
  'Bamuhigire',
  '+256700000000',
  'active',
  1,
  0
);

SET FOREIGN_KEY_CHECKS = 1;
