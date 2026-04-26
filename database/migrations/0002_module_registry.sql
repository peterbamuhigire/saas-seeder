-- SaaS Seeder Template - Module registry
SET NAMES utf8mb4;

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

INSERT INTO `tbl_modules` (`code`, `name`, `version`, `is_core`, `status`) VALUES
('AUTH', 'Authentication', '1.0.0', 1, 'active'),
('RBAC', 'Roles and permissions', '1.0.0', 1, 'active'),
('TENANT', 'Tenant management', '1.0.0', 1, 'active'),
('DASHBOARD', 'Dashboard', '1.0.0', 1, 'active')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `version` = VALUES(`version`),
  `is_core` = VALUES(`is_core`),
  `status` = VALUES(`status`);

INSERT INTO `tbl_module_dependencies` (`module_code`, `depends_on_module_code`) VALUES
('RBAC', 'AUTH'),
('TENANT', 'AUTH'),
('DASHBOARD', 'AUTH')
ON DUPLICATE KEY UPDATE `depends_on_module_code` = VALUES(`depends_on_module_code`);
